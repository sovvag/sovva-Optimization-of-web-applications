<?php
class Appointment extends Model
{
    const MAX_ACTIVE = 5;     // сколько будущих записей может быть у одного пациента
    const STEP = 10;          // шаг переноса записи, минут
    const MAX_DURATION = 180; // самая длинная запись, минут

    // ============================================================
    // ПАЦИЕНТ
    // ============================================================

    // Создать заявку. Всё в ТРАНЗАКЦИИ с блокировкой слота (FOR UPDATE):
    // если два пациента нажмут на одно время одновременно,
    // второй дождётся первого и увидит, что слот уже занят.
    public function createForUser($userId, $slotId)
    {
        $patient = $this->fetchOne('SELECT id FROM patients WHERE user_id = ?', [(int)$userId]);
        if (!$patient) {
            throw new RuntimeException('Не найдена карточка пациента.');
        }

        $today = date('Y-m-d');
        $now   = date('H:i:s');

        $this->db->beginTransaction();
        try {
            $slot = (new Slot())->lockSlot($slotId);

            if (!$slot || (int)$slot['doctor_active'] !== 1) {
                throw new DomainException('Врач не найден или не принимает.');
            }
            if ($slot['status'] !== 'free') {
                throw new DomainException('Это время уже занято. Выберите другое.');
            }
            if ($slot['slot_date'] < $today || ($slot['slot_date'] === $today && $slot['slot_time'] <= $now)) {
                throw new DomainException('Это время уже прошло.');
            }

            // Ограничение: не больше MAX_ACTIVE активных будущих записей (защита от спама)
            $row = $this->fetchOne(
                "SELECT COUNT(*) AS cnt
                 FROM appointments a
                 JOIN slots s ON s.id = a.slot_id
                 WHERE a.patient_id = ? AND a.status IN ('pending', 'confirmed') AND s.slot_date >= ?",
                [(int)$patient['id'], $today]
            );
            if ((int)$row['cnt'] >= self::MAX_ACTIVE) {
                throw new DomainException('У вас уже ' . self::MAX_ACTIVE . ' активных записей. Отмените лишнюю или дождитесь приёма.');
            }

            // Цена берётся из БД, а не из браузера
            $this->run(
                "INSERT INTO appointments (patient_id, doctor_id, slot_id, start_time, duration_minutes, status, price)
                 VALUES (?, ?, ?, ?, ?, 'pending', ?)",
                [(int)$patient['id'], (int)$slot['doctor_id'], (int)$slot['id'],
                 $slot['slot_time'], (int)$slot['slot_minutes'], $slot['price']]
            );
            $appointmentId = (int)$this->db->lastInsertId();

            $this->run("INSERT INTO notifications (appointment_id, type) VALUES (?, 'new_appointment')", [$appointmentId]);
            (new Slot())->syncDay($slot['doctor_id'], $slot['slot_date']);
            (new AuditLog())->add((int)$userId, 'appointment.create', 'appointment', $appointmentId, 'Заявка от пациента');

            $this->db->commit();
            return $appointmentId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Все записи пациента (по id его аккаунта)
    public function forPatient($userId)
    {
        return $this->fetchAll(
            'SELECT a.id, a.status, a.price, a.reason, sl.slot_date,
                    COALESCE(a.start_time, sl.slot_time) AS slot_time,
                    d.last_name, d.first_name, d.middle_name, d.slug, sp.name AS specialty
             FROM appointments a
             JOIN patients p ON p.id = a.patient_id
             JOIN slots sl ON sl.id = a.slot_id
             JOIN doctors d ON d.id = a.doctor_id
             JOIN specialties sp ON sp.id = d.specialty_id
             WHERE p.user_id = ?
             ORDER BY sl.slot_date DESC, slot_time DESC',
            [(int)$userId]
        );
    }

    // Отмена записи пациентом.
    // В запросе есть условие p.user_id = ?: пациент не сможет отменить ЧУЖУЮ запись,
    // даже если подменит номер в адресе (защита от горизонтального повышения прав).
    public function cancelByPatient($appointmentId, $userId)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT a.id, a.doctor_id, a.status, sl.slot_date,
                        COALESCE(a.start_time, sl.slot_time) AS start_time
                 FROM appointments a
                 JOIN patients p ON p.id = a.patient_id
                 JOIN slots sl ON sl.id = a.slot_id
                 WHERE a.id = ? AND p.user_id = ? FOR UPDATE"
            );
            $stmt->execute([(int)$appointmentId, (int)$userId]);
            $app = $stmt->fetch();

            if (!$app) {
                throw new OutOfBoundsException('not_found');
            }
            if ($app['status'] !== 'pending' && $app['status'] !== 'confirmed') {
                throw new DomainException('Эту запись отменить нельзя.');
            }
            if ($app['slot_date'] < date('Y-m-d')
                || ($app['slot_date'] === date('Y-m-d') && $app['start_time'] <= date('H:i:s'))) {
                throw new DomainException('Нельзя отменить запись, время которой уже прошло.');
            }

            $this->run("UPDATE appointments SET status = 'cancelled' WHERE id = ?", [(int)$appointmentId]);
            $this->run('UPDATE notifications SET is_read = 1 WHERE appointment_id = ?', [(int)$appointmentId]);
            (new Slot())->syncDay($app['doctor_id'], $app['slot_date']);   // время снова доступно
            (new AuditLog())->add((int)$userId, 'appointment.cancel_by_patient', 'appointment', (int)$appointmentId, null);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // ============================================================
    // АДМИНИСТРАТОР
    // ============================================================

    // Все активные записи одного дня (для таблицы расписания)
    public function forDay($date)
    {
        return $this->fetchAll(
            "SELECT a.id, a.status, a.doctor_id, a.reason, a.note, a.price,
                    COALESCE(a.start_time, s.slot_time) AS start_time,
                    COALESCE(a.duration_minutes, ws.slot_minutes) AS duration,
                    p.id AS patient_id, p.last_name, p.first_name, p.middle_name, p.phone
             FROM appointments a
             JOIN slots s ON s.id = a.slot_id
             JOIN work_schedules ws ON ws.id = s.schedule_id
             JOIN patients p ON p.id = a.patient_id
             WHERE s.slot_date = ? AND a.status IN ('pending', 'confirmed', 'completed')
             ORDER BY start_time",
            [$date]
        );
    }

    // Одобрить или отклонить заявку
    public function changeStatus($appointmentId, $newStatus, $userId)
    {
        if (!in_array($newStatus, ['confirmed', 'rejected'], true)) {
            throw new InvalidArgumentException('Недопустимый статус.');
        }

        $this->db->beginTransaction();
        try {
            $a = $this->lockInfo($appointmentId);
            if ($a['status'] !== 'pending') {
                throw new DomainException('Эта заявка уже обработана.');
            }

            $this->run(
                'UPDATE appointments SET status = ?, confirmed_by = ?, confirmed_at = NOW() WHERE id = ?',
                [$newStatus, (int)$userId, (int)$appointmentId]
            );
            (new Slot())->syncDay($a['doctor_id'], $a['slot_date']);   // подтверждённое время станет booked

            $action  = $newStatus === 'confirmed' ? 'appointment.confirm' : 'appointment.reject';
            $details = $newStatus === 'confirmed' ? 'Заявка подтверждена администратором' : 'Заявка отклонена администратором';
            (new AuditLog())->add((int)$userId, $action, 'appointment', (int)$appointmentId, $details);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Сдвинуть запись на $delta минут (-10 или +10)
    public function shift($appointmentId, $delta, $userId)
    {
        if ($delta !== self::STEP && $delta !== -self::STEP) {
            throw new InvalidArgumentException('Недопустимый шаг.');
        }

        $this->db->beginTransaction();
        try {
            $a = $this->lockInfo($appointmentId);
            $this->assertEditable($a);

            $start = time_to_minutes($a['start_time']) + $delta;
            $this->assertFits($a, $start, (int)$a['duration']);

            $this->run('UPDATE appointments SET start_time = ? WHERE id = ?',
                [minutes_to_time($start) . ':00', (int)$appointmentId]);
            (new Slot())->syncDay($a['doctor_id'], $a['slot_date']);
            (new AuditLog())->add((int)$userId, 'appointment.move', 'appointment', (int)$appointmentId,
                'Запись перенесена на ' . minutes_to_time($start));

            $this->db->commit();
            return ['start' => minutes_to_time($start), 'duration' => (int)$a['duration']];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Изменить повод, комментарий и длительность
    public function updateDetails($appointmentId, $reason, $note, $duration, $userId)
    {
        if (!array_key_exists($reason, reason_options())) {
            throw new InvalidArgumentException('Выберите повод из списка.');
        }
        if ($duration < self::STEP || $duration > self::MAX_DURATION || $duration % self::STEP !== 0) {
            throw new InvalidArgumentException('Длительность: от 10 до 180 минут с шагом 10.');
        }
        if (mb_strlen($note) > 500) {
            throw new InvalidArgumentException('Комментарий не длиннее 500 символов.');
        }

        $this->db->beginTransaction();
        try {
            $a = $this->lockInfo($appointmentId);
            $this->assertEditable($a);
            $this->assertFits($a, time_to_minutes($a['start_time']), $duration);

            $this->run(
                'UPDATE appointments SET reason = ?, note = ?, duration_minutes = ? WHERE id = ?',
                [$reason, $note !== '' ? $note : null, $duration, (int)$appointmentId]
            );
            (new Slot())->syncDay($a['doctor_id'], $a['slot_date']);
            (new AuditLog())->add((int)$userId, 'appointment.update', 'appointment', (int)$appointmentId, 'Изменены данные записи');

            $this->db->commit();
            return ['start' => substr($a['start_time'], 0, 5), 'duration' => $duration];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Удалить запись (завершённые приёмы удалять нельзя: по ним считается выручка)
    public function deleteByStaff($appointmentId, $userId)
    {
        $this->db->beginTransaction();
        try {
            $a = $this->lockInfo($appointmentId);
            if ($a['status'] === 'completed') {
                throw new DomainException('Завершённый приём удалить нельзя.');
            }

            $this->run('DELETE FROM appointments WHERE id = ?', [(int)$appointmentId]);
            (new Slot())->syncDay($a['doctor_id'], $a['slot_date']);
            (new AuditLog())->add((int)$userId, 'appointment.delete', 'appointment', (int)$appointmentId,
                'Удалена запись на ' . $a['slot_date'] . ' ' . substr($a['start_time'], 0, 5));

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Администратор сам записывает пациента (запись сразу подтверждена)
    public function createByStaff($userId, $patientId, $doctorId, $date, $time, $duration, $reason, $note)
    {
        if (!array_key_exists($reason, reason_options())) {
            throw new InvalidArgumentException('Выберите повод из списка.');
        }
        if ($duration < self::STEP || $duration > self::MAX_DURATION || $duration % self::STEP !== 0) {
            throw new InvalidArgumentException('Длительность: от 10 до 180 минут с шагом 10.');
        }
        if (mb_strlen($note) > 500) {
            throw new InvalidArgumentException('Комментарий не длиннее 500 символов.');
        }
        $start = time_to_minutes($time);
        if ($start % self::STEP !== 0) {
            throw new InvalidArgumentException('Время должно быть кратно 10 минутам.');
        }
        if ($date < date('Y-m-d')) {
            throw new DomainException('Нельзя записать на прошедшую дату.');
        }

        $this->db->beginTransaction();
        try {
            $patient = $this->fetchOne('SELECT id FROM patients WHERE id = ? FOR UPDATE', [(int)$patientId]);
            if (!$patient) {
                throw new DomainException('Выберите пациента из картотеки.');
            }
            $doctor = $this->fetchOne('SELECT id, price FROM doctors WHERE id = ? AND is_active = 1', [(int)$doctorId]);
            if (!$doctor) {
                throw new DomainException('Врач не найден.');
            }
            $shift = $this->fetchOne(
                'SELECT id, start_time, end_time FROM work_schedules WHERE doctor_id = ? AND work_date = ? FOR UPDATE',
                [(int)$doctorId, $date]
            );
            if (!$shift) {
                throw new DomainException('В этот день врач не работает.');
            }

            // Слот, в который попадает начало приёма (нужен для связи записи с графиком)
            $slotRow = null;
            $slots = $this->fetchAll(
                'SELECT s.id, s.slot_time, ws.slot_minutes
                 FROM slots s JOIN work_schedules ws ON ws.id = s.schedule_id
                 WHERE s.doctor_id = ? AND s.slot_date = ?',
                [(int)$doctorId, $date]
            );
            foreach ($slots as $s) {
                $sStart = time_to_minutes($s['slot_time']);
                if ($start >= $sStart && $start < $sStart + (int)$s['slot_minutes']) {
                    $slotRow = $s;
                }
            }
            if (!$slotRow) {
                throw new DomainException('В это время врач не принимает.');
            }

            $fake = ['doctor_id' => $doctorId, 'slot_date' => $date, 'id' => 0,
                     'shift_start' => $shift['start_time'], 'shift_end' => $shift['end_time']];
            $this->assertFits($fake, $start, $duration);

            $this->run(
                "INSERT INTO appointments (patient_id, doctor_id, slot_id, start_time, duration_minutes, reason, note,
                                           status, price, confirmed_by, confirmed_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, NOW())",
                [(int)$patientId, (int)$doctorId, (int)$slotRow['id'], minutes_to_time($start) . ':00', $duration,
                 $reason, $note !== '' ? $note : null, $doctor['price'], (int)$userId]
            );
            $id = (int)$this->db->lastInsertId();

            (new Slot())->syncDay($doctorId, $date);
            (new AuditLog())->add((int)$userId, 'appointment.create_by_admin', 'appointment', $id,
                'Администратор записал пациента на ' . $date . ' ' . minutes_to_time($start));

            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Сколько заявок ждёт решения (за все дни)
    public function countPending()
    {
        $row = $this->fetchOne("SELECT COUNT(*) AS n FROM appointments WHERE status = 'pending'");
        return (int)$row['n'];
    }

    // ============================================================
    // Вспомогательные методы
    // ============================================================

    // Запись со всеми данными для проверок. Блокирует строку (FOR UPDATE).
    private function lockInfo($appointmentId)
    {
        $stmt = $this->db->prepare(
            'SELECT a.id, a.status, a.doctor_id, s.slot_date,
                    COALESCE(a.start_time, s.slot_time) AS start_time,
                    COALESCE(a.duration_minutes, ws.slot_minutes) AS duration,
                    ws.start_time AS shift_start, ws.end_time AS shift_end
             FROM appointments a
             JOIN slots s ON s.id = a.slot_id
             JOIN work_schedules ws ON ws.id = s.schedule_id
             WHERE a.id = ? FOR UPDATE'
        );
        $stmt->execute([(int)$appointmentId]);
        $a = $stmt->fetch();
        if (!$a) {
            throw new DomainException('Запись не найдена.');
        }
        return $a;
    }

    private function assertEditable($a)
    {
        if ($a['status'] !== 'pending' && $a['status'] !== 'confirmed') {
            throw new DomainException('Эту запись изменить нельзя.');
        }
    }

    // Запись должна помещаться в смену врача и не пересекаться с другими записями
    private function assertFits($a, $start, $duration)
    {
        $end = $start + $duration;
        if ($start < time_to_minutes($a['shift_start']) || $end > time_to_minutes($a['shift_end'])) {
            throw new DomainException('Нельзя выйти за пределы смены врача.');
        }

        $others = $this->fetchAll(
            "SELECT COALESCE(a.start_time, s.slot_time) AS st,
                    COALESCE(a.duration_minutes, ws.slot_minutes) AS dur
             FROM appointments a
             JOIN slots s ON s.id = a.slot_id
             JOIN work_schedules ws ON ws.id = s.schedule_id
             WHERE a.doctor_id = ? AND s.slot_date = ? AND a.id <> ?
               AND a.status IN ('pending', 'confirmed', 'completed')",
            [(int)$a['doctor_id'], $a['slot_date'], (int)$a['id']]
        );
        foreach ($others as $o) {
            $oStart = time_to_minutes($o['st']);
            $oEnd   = $oStart + (int)$o['dur'];
            if ($start < $oEnd && $end > $oStart) {
                throw new DomainException('Это время занято другой записью.');
            }
        }
    }
}

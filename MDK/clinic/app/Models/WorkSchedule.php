<?php
// График работы врачей. Смена = один день врача (начало, конец, длина приёма).
// При создании смены автоматически создаются слоты (ячейки времени для записи).
class WorkSchedule extends Model
{
    // Смены врача за месяц; apps = сколько записей (любых) уже привязано к смене
    public function forDoctorMonth($doctorId, $month)
    {
        $from = $month . '-01';
        $to   = date('Y-m-t', strtotime($from));
        return $this->fetchAll(
            'SELECT ws.*,
                    (SELECT COUNT(*) FROM appointments a JOIN slots s ON s.id = a.slot_id
                     WHERE s.schedule_id = ws.id) AS apps
             FROM work_schedules ws
             WHERE ws.doctor_id = ? AND ws.work_date BETWEEN ? AND ?
             ORDER BY ws.work_date',
            [(int)$doctorId, $from, $to]
        );
    }

    public function find($id)
    {
        return $this->fetchOne(
            'SELECT ws.*,
                    (SELECT COUNT(*) FROM appointments a JOIN slots s ON s.id = a.slot_id
                     WHERE s.schedule_id = ws.id) AS apps
             FROM work_schedules ws WHERE ws.id = ?',
            [(int)$id]
        );
    }

    // Проверка времени смены. Возвращает текст ошибки или пустую строку.
    public static function checkTimes($start, $end, $minutes)
    {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $start) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $end)) {
            return 'Укажите время начала и конца смены.';
        }
        $s = time_to_minutes($start);
        $e = time_to_minutes($end);
        if ($s % 10 !== 0 || $e % 10 !== 0) {
            return 'Время должно быть кратно 10 минутам.';
        }
        if ($minutes < 10 || $minutes > 180 || $minutes % 10 !== 0) {
            return 'Длительность приёма: от 10 до 180 минут с шагом 10.';
        }
        if ($e - $s < $minutes) {
            return 'Смена короче одного приёма. Проверьте время.';
        }
        return '';
    }

    // Создать смену и слоты. Если смена на эту дату уже есть, вернёт null.
    public function create($doctorId, $date, $start, $end, $minutes)
    {
        if ($this->fetchOne('SELECT id FROM work_schedules WHERE doctor_id = ? AND work_date = ?', [(int)$doctorId, $date])) {
            return null;
        }
        $this->db->beginTransaction();
        try {
            $this->run(
                'INSERT INTO work_schedules (doctor_id, work_date, start_time, end_time, slot_minutes) VALUES (?, ?, ?, ?, ?)',
                [(int)$doctorId, $date, $start . ':00', $end . ':00', (int)$minutes]
            );
            $id = (int)$this->db->lastInsertId();
            $this->makeSlots($id, $doctorId, $date, $start, $end, $minutes);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Изменить смену (только если записей нет): слоты создаются заново
    public function update($id, $start, $end, $minutes)
    {
        $this->db->beginTransaction();
        try {
            $ws = $this->fetchOne('SELECT * FROM work_schedules WHERE id = ? FOR UPDATE', [(int)$id]);
            $this->run('DELETE FROM slots WHERE schedule_id = ?', [(int)$id]);
            $this->run('UPDATE work_schedules SET start_time = ?, end_time = ?, slot_minutes = ? WHERE id = ?',
                [$start . ':00', $end . ':00', (int)$minutes, (int)$id]);
            $this->makeSlots($id, $ws['doctor_id'], $ws['work_date'], $start, $end, $minutes);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Удалить смену (слоты удаляются вместе с ней: так задано в базе данных)
    public function delete($id)
    {
        $this->run('DELETE FROM work_schedules WHERE id = ?', [(int)$id]);
    }

    // Слоты: от начала смены с шагом $minutes, пока приём помещается до конца смены
    private function makeSlots($scheduleId, $doctorId, $date, $start, $end, $minutes)
    {
        $endMin = time_to_minutes($end);
        for ($t = time_to_minutes($start); $t + $minutes <= $endMin; $t += $minutes) {
            $this->run(
                "INSERT INTO slots (doctor_id, schedule_id, slot_date, slot_time, status) VALUES (?, ?, ?, ?, 'free')",
                [(int)$doctorId, (int)$scheduleId, $date, minutes_to_time($t) . ':00']
            );
        }
    }
}

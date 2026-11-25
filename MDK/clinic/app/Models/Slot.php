<?php
class Slot extends Model
{
    // Свободное время врача на месяц (для календаря пациента).
    // Занятые и ожидающие слоты НЕ возвращаются: пациент их просто не видит.
    // Время, которое уже прошло сегодня, тоже не возвращается.
    public function availableForMonth($doctorId, $month, $today, $nowTime)
    {
        $from = $month . '-01';
        $to   = date('Y-m-t', strtotime($from));

        return $this->fetchAll(
            "SELECT id, slot_date, slot_time
             FROM slots
             WHERE doctor_id = ? AND status = 'free'
               AND slot_date BETWEEN ? AND ?
               AND (slot_date > ? OR (slot_date = ? AND slot_time > ?))
             ORDER BY slot_date, slot_time",
            [(int)$doctorId, $from, $to, $today, $today, $nowTime]
        );
    }

    // Блокируем строку слота на время транзакции (FOR UPDATE)
    public function lockSlot($id)
    {
        $stmt = $this->db->prepare(
            'SELECT sl.*, ws.slot_minutes, d.is_active AS doctor_active, d.price
             FROM slots sl
             JOIN work_schedules ws ON ws.id = sl.schedule_id
             JOIN doctors d ON d.id = sl.doctor_id
             WHERE sl.id = ? FOR UPDATE'
        );
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    // Врачи, которые работают в этот день (для колонок таблицы расписания)
    public function doctorsForDay($date)
    {
        return $this->fetchAll(
            'SELECT ws.doctor_id, ws.start_time, ws.end_time, ws.slot_minutes,
                    d.last_name AS doctor_last_name, d.first_name AS doctor_first_name,
                    d.middle_name AS doctor_middle_name, sp.name AS specialty
             FROM work_schedules ws
             JOIN doctors d ON d.id = ws.doctor_id AND d.is_active = 1
             JOIN specialties sp ON sp.id = d.specialty_id
             WHERE ws.work_date = ?
             ORDER BY d.last_name, d.first_name',
            [$date]
        );
    }

    // Все слоты дня
    public function slotsForDay($date)
    {
        return $this->fetchAll(
            'SELECT s.id, s.doctor_id, s.slot_time, s.status, ws.slot_minutes
             FROM slots s JOIN work_schedules ws ON ws.id = s.schedule_id
             WHERE s.slot_date = ?
             ORDER BY s.doctor_id, s.slot_time',
            [$date]
        );
    }

    // --------------------------------------------------------
    // Пересчёт статусов слотов врача на день.
    // Слот занят, если его время ПЕРЕСЕКАЕТСЯ с активной записью:
    //   подтверждённая или завершённая запись -> booked
    //   только ожидающая заявка               -> pending
    //   иначе                                 -> free
    // Благодаря этому запись можно сдвигать на 10 минут, а пациент
    // всё равно не увидит занятое время. Вызывать внутри транзакции.
    // --------------------------------------------------------
    public function syncDay($doctorId, $date)
    {
        $slots = $this->fetchAll(
            'SELECT s.id, s.slot_time, s.status, ws.slot_minutes
             FROM slots s JOIN work_schedules ws ON ws.id = s.schedule_id
             WHERE s.doctor_id = ? AND s.slot_date = ?',
            [(int)$doctorId, $date]
        );
        $apps = $this->fetchAll(
            "SELECT a.status, COALESCE(a.start_time, s.slot_time) AS st,
                    COALESCE(a.duration_minutes, ws.slot_minutes) AS dur
             FROM appointments a
             JOIN slots s ON s.id = a.slot_id
             JOIN work_schedules ws ON ws.id = s.schedule_id
             WHERE a.doctor_id = ? AND s.slot_date = ?
               AND a.status IN ('pending', 'confirmed', 'completed')",
            [(int)$doctorId, $date]
        );

        foreach ($slots as $slot) {
            $sStart = time_to_minutes($slot['slot_time']);
            $sEnd   = $sStart + (int)$slot['slot_minutes'];
            $status = 'free';

            foreach ($apps as $a) {
                $aStart = time_to_minutes($a['st']);
                $aEnd   = $aStart + (int)$a['dur'];
                if ($aStart < $sEnd && $aEnd > $sStart) {      // времена пересекаются
                    if ($a['status'] === 'pending') {
                        if ($status === 'free') {
                            $status = 'pending';
                        }
                    } else {
                        $status = 'booked';
                    }
                }
            }
            if ($status !== $slot['status']) {
                $this->run('UPDATE slots SET status = ? WHERE id = ?', [$status, (int)$slot['id']]);
            }
        }
    }
}

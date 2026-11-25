<?php
// Отчёты руководителя. Все данные считаются запросами к базе за выбранный период.
class Report extends Model
{
    // По каждому врачу: смены, часы, записи, выручка.
    // Выручка = цена подтверждённых и завершённых приёмов.
    public function doctorStats($from, $to)
    {
        $doctors = $this->fetchAll(
            'SELECT d.id, d.last_name, d.first_name, d.middle_name, sp.name AS specialty
             FROM doctors d JOIN specialties sp ON sp.id = d.specialty_id
             ORDER BY d.last_name, d.first_name'
        );

        $shifts = $this->fetchAll(
            'SELECT doctor_id, COUNT(*) AS shifts,
                    SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) / 3600 AS hours
             FROM work_schedules WHERE work_date BETWEEN ? AND ?
             GROUP BY doctor_id',
            [$from, $to]
        );
        $apps = $this->fetchAll(
            "SELECT a.doctor_id, COUNT(*) AS cnt, SUM(a.price) AS revenue,
                    SUM(COALESCE(a.duration_minutes, ws.slot_minutes)) / 60 AS hours
             FROM appointments a
             JOIN slots s ON s.id = a.slot_id
             JOIN work_schedules ws ON ws.id = s.schedule_id
             WHERE s.slot_date BETWEEN ? AND ? AND a.status IN ('confirmed', 'completed')
             GROUP BY a.doctor_id",
            [$from, $to]
        );

        $byShift = [];
        foreach ($shifts as $r) {
            $byShift[$r['doctor_id']] = $r;
        }
        $byApp = [];
        foreach ($apps as $r) {
            $byApp[$r['doctor_id']] = $r;
        }

        $rows = [];
        foreach ($doctors as $d) {
            $id = $d['id'];
            $planned = isset($byShift[$id]) ? (float)$byShift[$id]['hours'] : 0;
            $booked  = isset($byApp[$id]) ? (float)$byApp[$id]['hours'] : 0;
            $rows[] = [
                'name'      => short_name($d),
                'specialty' => $d['specialty'],
                'shifts'    => isset($byShift[$id]) ? (int)$byShift[$id]['shifts'] : 0,
                'planned'   => $planned,
                'booked'    => $booked,
                'load'      => $planned > 0 ? round($booked / $planned * 100) : 0,
                'visits'    => isset($byApp[$id]) ? (int)$byApp[$id]['cnt'] : 0,
                'revenue'   => isset($byApp[$id]) ? (float)$byApp[$id]['revenue'] : 0,
            ];
        }
        return $rows;
    }

    // Действия администраторов (из журнала audit_log)
    public function adminActions($from, $to)
    {
        return $this->fetchAll(
            "SELECT u.last_name, u.first_name, u.middle_name,
                    COALESCE(SUM(l.action = 'appointment.create_by_admin'), 0) AS created,
                    COALESCE(SUM(l.action = 'appointment.delete'), 0) AS deleted,
                    COALESCE(SUM(l.action = 'appointment.confirm'), 0) AS confirmed,
                    COALESCE(SUM(l.action = 'appointment.reject'), 0) AS rejected,
                    COALESCE(SUM(l.action = 'appointment.move'), 0) AS moved
             FROM users u
             JOIN roles r ON r.id = u.role_id AND r.name = 'administrator'
             LEFT JOIN audit_log l ON l.user_id = u.id AND l.created_at >= ? AND l.created_at < ?
             GROUP BY u.id, u.last_name, u.first_name, u.middle_name
             ORDER BY u.last_name",
            [$from . ' 00:00:00', date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00']
        );
    }
}

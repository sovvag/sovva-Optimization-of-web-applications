<?php
class Notification extends Model
{
    // Непрочитанные уведомления о заявках, которые ещё ждут решения
    public function unreadForAdmin($limit = 5)
    {
        $limit = max(1, min(10, (int)$limit));   // число подставляем сами, поэтому приводим к int
        return $this->fetchAll(
            "SELECT n.id, n.appointment_id, sl.slot_date, COALESCE(a.start_time, sl.slot_time) AS slot_time,
                    p.last_name, p.first_name, p.middle_name,
                    d.last_name AS doctor_last_name, d.first_name AS doctor_first_name,
                    d.middle_name AS doctor_middle_name
             FROM notifications n
             JOIN appointments a ON a.id = n.appointment_id AND a.status = 'pending'
             JOIN patients p ON p.id = a.patient_id
             JOIN slots sl ON sl.id = a.slot_id
             JOIN doctors d ON d.id = a.doctor_id
             WHERE n.is_read = 0
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT " . $limit
        );
    }

    public function unreadCount()
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS n
             FROM notifications n JOIN appointments a ON a.id = n.appointment_id AND a.status = 'pending'
             WHERE n.is_read = 0"
        );
        return (int)$row['n'];
    }

    public function markRead($id)
    {
        $this->run('UPDATE notifications SET is_read = 1 WHERE id = ?', [(int)$id]);
    }

    public function markAppointmentRead($appointmentId)
    {
        $this->run('UPDATE notifications SET is_read = 1 WHERE appointment_id = ?', [(int)$appointmentId]);
    }
}

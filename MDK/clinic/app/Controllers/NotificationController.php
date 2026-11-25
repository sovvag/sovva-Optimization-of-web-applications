<?php
// JSON для всплывающих уведомлений администратора (запрашивает staff.js)
class NotificationController extends Controller
{
    // GET /api/notifications
    public function unread()
    {
        $model = new Notification();
        $items = [];
        foreach ($model->unreadForAdmin(5) as $row) {
            $items[] = [
                'id'             => (int)$row['id'],
                'appointment_id' => (int)$row['appointment_id'],
                'patient'        => short_name($row),
                'doctor'         => short_name([
                    'last_name'   => $row['doctor_last_name'],
                    'first_name'  => $row['doctor_first_name'],
                    'middle_name' => $row['doctor_middle_name'],
                ]),
                'when'           => date_ru($row['slot_date']) . ', ' . time_short($row['slot_time']),
            ];
        }
        $this->json(['success' => true, 'count' => $model->unreadCount(), 'items' => $items]);
    }

    // POST /api/notifications/{id}/read  (закрыть уведомление)
    public function read($id)
    {
        (new Notification())->markRead((int)$id);
        $this->json(['success' => true]);
    }
}

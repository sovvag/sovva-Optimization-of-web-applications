<?php
// Запись пациента на приём: API для календаря и страница "Мои записи"
class AppointmentController extends Controller
{
    // GET /api/slots?doctor_id=3&month=2026-11
    // Возвращает свободное время врача по дням: {"days": {"2026-11-12": [{"id": 5, "time": "09:00"}, ...]}}
    public function slots()
    {
        $id    = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);
        $month = isset($_GET['month']) && is_string($_GET['month']) ? $_GET['month'] : '';

        if (!$id || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->json(['success' => false, 'message' => 'Некорректные параметры.'], 422);
        }
        if (!(new Doctor())->findById($id)) {
            $this->json(['success' => false, 'message' => 'Врач не найден.'], 404);
        }

        $rows = (new Slot())->availableForMonth($id, $month, date('Y-m-d'), date('H:i:s'));

        $days = [];
        foreach ($rows as $row) {
            $days[$row['slot_date']][] = ['id' => (int)$row['id'], 'time' => time_short($row['slot_time'])];
        }
        $this->json(['success' => true, 'days' => $days]);
    }

    // POST /appointments   (поле slot_id)
    public function create()
    {
        $slotId = filter_var(post_str('slot_id'), FILTER_VALIDATE_INT);
        if (!$slotId) {
            $this->json(['success' => false, 'message' => 'Выберите время приёма.'], 422);
        }

        try {
            $id = (new Appointment())->createForUser(Auth::id(), $slotId);
            $this->json([
                'success' => true,
                'message' => 'Заявка отправлена и ожидает подтверждения администратора.',
                'id'      => $id,
            ], 201);
        } catch (DomainException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // GET /my-appointments
    public function mine()
    {
        $this->view('appointments/mine', [
            'title'        => 'Мои записи',
            'noindex'      => true,
            'appointments' => (new Appointment())->forPatient(Auth::id()),
            'today'        => date('Y-m-d'),
            'nowTime'      => date('H:i:s'),
        ]);
    }

    // POST /my-appointments/{id}/cancel
    public function cancel($id)
    {
        try {
            (new Appointment())->cancelByPatient((int)$id, Auth::id());
            flash('success', 'Запись отменена.');
        } catch (OutOfBoundsException $e) {
            $this->notFound();        // чужая или несуществующая запись: ведём себя одинаково
        } catch (DomainException $e) {
            flash('error', $e->getMessage());
        }
        redirect('/my-appointments');
    }
}

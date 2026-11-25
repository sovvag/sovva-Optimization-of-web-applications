<?php
// График работы врачей (руководитель): добавление, изменение и удаление смен.
class ScheduleController extends Controller
{
    // GET /manager/work-schedule?doctor=1&month=2026-11
    public function index()
    {
        $doctorModel = new Doctor();
        $doctors = array_values(array_filter($doctorModel->allForManage(), function ($d) {
            return (int)$d['is_active'] === 1;
        }));

        $doctorId = isset($_GET['doctor']) ? (int)$_GET['doctor'] : (empty($doctors) ? 0 : (int)$doctors[0]['id']);
        $month = (isset($_GET['month']) && is_string($_GET['month']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['month']))
            ? $_GET['month'] : date('Y-m');

        $selected = $doctorId ? $doctorModel->findAny($doctorId) : null;

        $this->view('manager/work_schedule', [
            'title'    => 'График работы',
            'noindex'  => true,
            'doctors'  => $doctors,
            'doctor'   => $selected,
            'month'    => $month,
            'prevMonth' => date('Y-m', strtotime($month . '-01 -1 month')),
            'nextMonth' => date('Y-m', strtotime($month . '-01 +1 month')),
            'shifts'   => $selected ? (new WorkSchedule())->forDoctorMonth($selected['id'], $month) : [],
            'scripts'  => ['js/work_schedule.js'],
        ]);
    }

    // POST /manager/work-schedule   (добавить смены на период по дням недели)
    public function store()
    {
        $doctorId = (int)post_str('doctor_id');
        $from     = post_str('date_from');
        $to       = post_str('date_to');
        $start    = post_str('start');
        $end      = post_str('end');
        $minutes  = (int)post_str('minutes');
        $weekdays = isset($_POST['weekdays']) && is_array($_POST['weekdays']) ? array_map('intval', $_POST['weekdays']) : [];

        $back = '/manager/work-schedule?doctor=' . $doctorId . '&month=' . (Validator::dateValue($from) ? substr($from, 0, 7) : date('Y-m'));

        $error = '';
        if (!(new Doctor())->findAny($doctorId)) {
            $error = 'Выберите врача.';
        } elseif (!Validator::dateValue($from) || !Validator::dateValue($to) || $to < $from) {
            $error = 'Проверьте период: «с» не может быть позже «по».';
        } elseif ($from < date('Y-m-d')) {
            $error = 'Нельзя добавить смену на прошедшую дату.';
        } elseif ((strtotime($to) - strtotime($from)) / 86400 > 92) {
            $error = 'Период не длиннее 3 месяцев.';
        } elseif (empty($weekdays)) {
            $error = 'Отметьте хотя бы один день недели.';
        } else {
            $error = WorkSchedule::checkTimes($start, $end, $minutes);
        }
        if ($error !== '') {
            flash('error', $error);
            redirect($back);
        }

        $model = new WorkSchedule();
        $created = 0;
        $skipped = 0;
        for ($t = strtotime($from); $t <= strtotime($to); $t += 86400) {
            if (!in_array((int)date('N', $t), $weekdays, true)) {
                continue;
            }
            if ($model->create($doctorId, date('Y-m-d', $t), $start, $end, $minutes) === null) {
                $skipped++;       // на эту дату смена уже есть
            } else {
                $created++;
            }
        }

        (new AuditLog())->add(Auth::id(), 'schedule.create', 'doctor', $doctorId, 'Добавлено смен: ' . $created);
        flash('success', 'Добавлено смен: ' . $created . ($skipped ? '. Пропущено (уже были): ' . $skipped : '') . '.');
        redirect($back);
    }

    // POST /manager/work-schedule/{id}/update
    public function update($id)
    {
        $shift = (new WorkSchedule())->find($id);
        if (!$shift) {
            $this->notFound();
        }
        $back = '/manager/work-schedule?doctor=' . (int)$shift['doctor_id'] . '&month=' . substr($shift['work_date'], 0, 7);

        $start = post_str('start');
        $end = post_str('end');
        $minutes = (int)post_str('minutes');

        if ((int)$shift['apps'] > 0) {
            flash('error', 'На этот день уже есть записи. Изменить смену нельзя.');
        } elseif (($error = WorkSchedule::checkTimes($start, $end, $minutes)) !== '') {
            flash('error', $error);
        } else {
            (new WorkSchedule())->update($id, $start, $end, $minutes);
            (new AuditLog())->add(Auth::id(), 'schedule.update', 'doctor', (int)$shift['doctor_id'], 'Изменена смена ' . $shift['work_date']);
            flash('success', 'Смена изменена.');
        }
        redirect($back);
    }

    // POST /manager/work-schedule/{id}/delete
    public function delete($id)
    {
        $shift = (new WorkSchedule())->find($id);
        if (!$shift) {
            $this->notFound();
        }
        $back = '/manager/work-schedule?doctor=' . (int)$shift['doctor_id'] . '&month=' . substr($shift['work_date'], 0, 7);

        if ((int)$shift['apps'] > 0) {
            flash('error', 'На этот день уже есть записи (в том числе отменённые). Удалить смену нельзя.');
        } else {
            (new WorkSchedule())->delete($id);
            (new AuditLog())->add(Auth::id(), 'schedule.delete', 'doctor', (int)$shift['doctor_id'], 'Удалена смена ' . $shift['work_date']);
            flash('success', 'Смена удалена.');
        }
        redirect($back);
    }
}

<?php
// Расписание для администратора и руководителя:
// таблица "врачи (колонки) x время (строки по 10 минут)".
class AdminScheduleController extends Controller
{
    const STEP = 10;   // одна строка таблицы = 10 минут

    // GET /schedule?date=2026-11-12
    public function index()
    {
        $date = (isset($_GET['date']) && Validator::dateValue($_GET['date'])) ? $_GET['date'] : date('Y-m-d');

        $slotModel = new Slot();
        $appointments = (new Appointment())->forDay($date);
        $doctors = $slotModel->doctorsForDay($date);

        $this->view('admin/schedule', [
            'title'        => 'Расписание',
            'noindex'      => true,
            'date'         => $date,
            'prevDate'     => date('Y-m-d', strtotime($date . ' -1 day')),
            'nextDate'     => date('Y-m-d', strtotime($date . ' +1 day')),
            'grid'         => $this->buildGrid($doctors, $slotModel->slotsForDay($date), $appointments),
            'dayTotal'     => count($appointments),
            'pendingTotal' => (new Appointment())->countPending(),
            'canApprove'   => Auth::can('appointment.approve'),
            'canManage'    => Auth::can('appointment.manage'),
            'styles'       => ['css/schedule.css'],
            'scripts'      => ['js/schedule.js'],
        ]);
    }

    // POST /schedule/appointments/{id}/status   (status: confirmed или rejected)
    public function updateStatus($id)
    {
        try {
            $status = post_str('status');
            (new Appointment())->changeStatus((int)$id, $status, Auth::id());
            (new Notification())->markAppointmentRead((int)$id);
            $message = $status === 'confirmed'
                ? 'Запись подтверждена. Время закреплено за пациентом.'
                : 'Заявка отклонена. Время снова доступно для записи.';
            $this->answer(true, $message);
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    // POST /schedule/appointments/{id}/move   (delta: -10 или 10)
    public function move($id)
    {
        try {
            $result = (new Appointment())->shift((int)$id, (int)post_str('delta'), Auth::id());
            $this->answer(true, 'Запись перенесена.', $this->timeInfo($result));
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    // POST /schedule/appointments/{id}/update   (reason, note, duration)
    public function update($id)
    {
        try {
            $result = (new Appointment())->updateDetails(
                (int)$id, post_str('reason'), post_str('note'), (int)post_str('duration'), Auth::id()
            );
            $this->answer(true, 'Изменения сохранены.', $this->timeInfo($result));
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    // POST /schedule/appointments/{id}/delete
    public function delete($id)
    {
        try {
            (new Appointment())->deleteByStaff((int)$id, Auth::id());
            $this->answer(true, 'Запись удалена.');
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    // POST /schedule/appointments   (кнопка "Запись": администратор сам записывает пациента)
    public function store()
    {
        try {
            $date = post_str('date');
            $time = post_str('time');
            if (!Validator::dateValue($date) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
                throw new InvalidArgumentException('Проверьте дату и время.');
            }
            $id = (new Appointment())->createByStaff(
                Auth::id(), (int)post_str('patient_id'), (int)post_str('doctor_id'), $date, $time,
                (int)post_str('duration'), post_str('reason'), post_str('note')
            );
            $this->answer(true, 'Пациент записан.', ['id' => $id]);
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    // ---------- Ответы ----------

    private function answer($ok, $message, $extra = [])
    {
        if (wants_json()) {
            $this->json(array_merge(['success' => $ok, 'message' => $message], $extra));
        }
        flash($ok ? 'success' : 'error', $message);
        $back = post_str('back_date');
        redirect('/schedule?date=' . (Validator::dateValue($back) ? $back : date('Y-m-d')));
    }

    // Превращаем исключение в понятный ответ (500-ю ошибку не скрываем)
    private function fail($e)
    {
        if ($e instanceof DomainException) {
            $this->answerError($e->getMessage(), 409);
        }
        if ($e instanceof InvalidArgumentException) {
            $this->answerError($e->getMessage(), 422);
        }
        throw $e;
    }

    private function answerError($message, $status)
    {
        if (wants_json()) {
            $this->json(['success' => false, 'message' => $message], $status);
        }
        flash('error', $message);
        redirect('/schedule');
    }

    private function timeInfo($result)
    {
        $end = minutes_to_time(time_to_minutes($result['start']) + $result['duration']);
        return ['appointment' => [
            'start' => $result['start'], 'end' => $end, 'duration' => $result['duration'],
        ]];
    }

    // --------------------------------------------------------
    // Строим таблицу расписания. Одна строка = 10 минут.
    //   null   - врач в это время не работает (серая ячейка)
    //   'skip' - ячейку занимает блок из строки выше (rowspan)
    //   ['type' => 'free', ...] - свободное время
    //   ['type' => 'app',  ...] - запись пациента
    // --------------------------------------------------------
    private function buildGrid($doctors, $slots, $appointments)
    {
        if (empty($doctors)) {
            return null;
        }

        // 1. Границы дня
        $gridStart = null;
        $gridEnd = null;
        foreach ($doctors as $d) {
            $s = time_to_minutes($d['start_time']);
            $e = time_to_minutes($d['end_time']);
            $gridStart = ($gridStart === null || $s < $gridStart) ? $s : $gridStart;
            $gridEnd   = ($gridEnd === null || $e > $gridEnd) ? $e : $gridEnd;
        }
        $gridStart = (int)(floor($gridStart / self::STEP) * self::STEP);
        $gridEnd   = (int)(ceil($gridEnd / self::STEP) * self::STEP);
        $rowCount  = (int)(($gridEnd - $gridStart) / self::STEP);

        // 2. Колонки (по одной на врача) и счётчик занятости
        $columns = [];
        $colIndex = [];
        foreach ($doctors as $i => $d) {
            $colIndex[(int)$d['doctor_id']] = $i;
            $columns[$i] = [
                'id'        => (int)$d['doctor_id'],
                'name'      => short_name(['last_name' => $d['doctor_last_name'], 'first_name' => $d['doctor_first_name'], 'middle_name' => $d['doctor_middle_name']]),
                'specialty' => $d['specialty'],
                'minutes'   => (int)$d['slot_minutes'],
                'busy'      => 0,
                'total'     => 0,
            ];
        }

        // 3. Пустая таблица
        $cells = [];
        for ($r = 0; $r < $rowCount; $r++) {
            for ($c = 0; $c < count($columns); $c++) {
                $cells[$r][$c] = null;
            }
        }

        // 4. Рабочее время врача: сначала всё свободно (ключ 0), затем слоты со своими ключами.
        //    Ключ нужен, чтобы склеить соседние свободные строки в одну ячейку.
        foreach ($doctors as $c => $d) {
            for ($m = time_to_minutes($d['start_time']); $m < time_to_minutes($d['end_time']); $m += self::STEP) {
                $cells[(int)(($m - $gridStart) / self::STEP)][$c] = ['type' => 'free', 'key' => 0, 'start' => $m, 'span' => 1];
            }
        }
        foreach ($slots as $s) {
            if (!isset($colIndex[(int)$s['doctor_id']])) {
                continue;
            }
            $c = $colIndex[(int)$s['doctor_id']];
            $columns[$c]['total']++;
            if ($s['status'] !== 'free') {
                $columns[$c]['busy']++;
            }
            $from = time_to_minutes($s['slot_time']);
            for ($m = $from; $m < $from + (int)$s['slot_minutes']; $m += self::STEP) {
                $r = (int)(($m - $gridStart) / self::STEP);
                if (isset($cells[$r][$c]) && is_array($cells[$r][$c])) {
                    $cells[$r][$c]['key'] = (int)$s['id'];
                }
            }
        }

        // 5. Записи пациентов поверх свободного времени
        foreach ($appointments as $a) {
            if (!isset($colIndex[(int)$a['doctor_id']])) {
                continue;
            }
            $c = $colIndex[(int)$a['doctor_id']];
            $start = time_to_minutes($a['start_time']);
            $r = (int)(($start - $gridStart) / self::STEP);
            $span = max(1, (int)ceil((int)$a['duration'] / self::STEP));
            if (!isset($cells[$r])) {
                continue;
            }
            $a['type'] = 'app';
            $a['span'] = min($span, $rowCount - $r);
            $a['start_min'] = $start;
            $cells[$r][$c] = $a;
            for ($k = 1; $k < $a['span']; $k++) {
                $cells[$r + $k][$c] = 'skip';
            }
        }

        // 6. Склеиваем подряд идущие свободные строки одного слота
        for ($c = 0; $c < count($columns); $c++) {
            $head = null;
            for ($r = 0; $r < $rowCount; $r++) {
                $cell = $cells[$r][$c];
                if (is_array($cell) && $cell['type'] === 'free') {
                    if ($head !== null && $cells[$head][$c]['key'] === $cell['key']) {
                        $cells[$head][$c]['span']++;
                        $cells[$r][$c] = 'skip';
                    } else {
                        $head = $r;
                    }
                } else {
                    $head = null;
                }
            }
        }

        // 7. Строки с подписями времени (подпись только на :00 и :30)
        $rows = [];
        for ($r = 0; $r < $rowCount; $r++) {
            $m = $gridStart + $r * self::STEP;
            $rows[] = [
                'label' => ($m % 30 === 0) ? minutes_to_time($m) : '',
                'kind'  => ($m % 60 === 0) ? 'hour' : (($m % 30 === 0) ? 'half' : ''),
                'cells' => $cells[$r],
            ];
        }

        return ['columns' => $columns, 'rows' => $rows];
    }
}

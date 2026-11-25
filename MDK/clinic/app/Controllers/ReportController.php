<?php
// Отчёты руководителя: выработка часов, прибыль, действия администраторов.
class ReportController extends Controller
{
    // GET /manager/reports?tab=revenue&period=month
    public function index()
    {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'hours';
        if (!in_array($tab, ['hours', 'revenue', 'actions'], true)) {
            $tab = 'hours';
        }
        $period = isset($_GET['period']) ? $_GET['period'] : 'month';
        if (!in_array($period, ['day', 'week', 'month', 'custom'], true)) {
            $period = 'month';
        }

        // Границы периода
        $today = date('Y-m-d');
        if ($period === 'day') {
            $from = $to = $today;
        } elseif ($period === 'week') {
            $from = date('Y-m-d', strtotime('monday this week'));
            $to   = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($period === 'month') {
            $from = date('Y-m-01');
            $to   = date('Y-m-t');
        } else {
            $from = isset($_GET['from']) ? $_GET['from'] : $today;
            $to   = isset($_GET['to']) ? $_GET['to'] : $today;
            if (!Validator::dateValue($from) || !Validator::dateValue($to) || $to < $from
                || (strtotime($to) - strtotime($from)) / 86400 > 366) {
                flash('error', 'Проверьте период: «с» не позже «по», не больше года.');
                $from = $to = $today;
            }
        }

        $report = new Report();
        if ($tab === 'actions') {
            $rows = $report->adminActions($from, $to);
        } else {
            $rows = $report->doctorStats($from, $to);
        }

        if (isset($_GET['export'])) {
            $this->exportCsv($tab, $rows, $from, $to);
        }

        $this->view('manager/reports', [
            'title'   => 'Отчёты',
            'noindex' => true,
            'tab'     => $tab,
            'period'  => $period,
            'from'    => $from,
            'to'      => $to,
            'rows'    => $rows,
        ]);
    }

    // Выгрузка в CSV (открывается в Excel)
    private function exportCsv($tab, $rows, $from, $to)
    {
        if ($tab === 'actions') {
            $head = ['Администратор', 'Добавлено записей', 'Удалено записей', 'Подтверждено', 'Отклонено', 'Перенесено'];
            $data = [];
            foreach ($rows as $r) {
                $data[] = [short_name($r), $r['created'], $r['deleted'], $r['confirmed'], $r['rejected'], $r['moved']];
            }
        } else {
            $head = ['Врач', 'Должность', 'Смен', 'Часов по графику', 'Часов записано', 'Загрузка, %', 'Приёмов', 'Выручка, руб.'];
            $data = [];
            foreach ($rows as $r) {
                $data[] = [$r['name'], $r['specialty'], $r['shifts'], round($r['planned'], 1), round($r['booked'], 1),
                           $r['load'], $r['visits'], $r['revenue']];
            }
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="report-' . $tab . '-' . $from . '_' . $to . '.csv"');
        echo "\xEF\xBB\xBF";                       // метка, чтобы Excel правильно показал русские буквы
        $out = fopen('php://output', 'w');
        fputcsv($out, $head, ';');
        foreach ($data as $line) {
            // Защита от "CSV-инъекции": ячейка не должна начинаться с символов формулы
            foreach ($line as $i => $cell) {
                if (is_string($cell) && $cell !== '' && strpos('=+-@', $cell[0]) !== false) {
                    $line[$i] = "'" . $cell;
                }
            }
            fputcsv($out, $line, ';');
        }
        fclose($out);
        exit;
    }
}

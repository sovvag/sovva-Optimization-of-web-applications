<?php
class DoctorController extends Controller
{
    // Список всех врачей: /doctors
    public function index()
    {
        $doctorModel = new Doctor();

        $this->view('doctors/index', [
            'title'       => 'Врачи клиники: офтальмологи, офтальмохирурги, ретинологи',
            'description' => 'Выберите врача и запишитесь на приём онлайн. '
                           . 'Опытные офтальмологи, офтальмохирурги и ретинологи.',
            'doctors'     => $doctorModel->allActive(),
        ]);
    }

    // Карточка одного врача: /doctors/smirnova-elena
    // ЭТО И ЕСТЬ ШАБЛОН: один метод и один файл show.php для всех врачей.
    public function show($slug)
    {
        $doctorModel = new Doctor();
        $doctor = $doctorModel->findBySlug($slug);

        if ($doctor === null) {
            $this->notFound();
        }

        $this->view('doctors/show', [
            'title'       => full_name($doctor) . ', ' . mb_strtolower($doctor['specialty']) . ': запись на приём',
            'description' => full_name($doctor) . ', ' . mb_strtolower($doctor['specialty'])
                           . ', стаж ' . years_text($doctor['experience_years'])
                           . '. Запишитесь на приём онлайн.',
            'canonical'   => absolute_url('/doctors/' . $doctor['slug']),
            'doctor'      => $doctor,
            'styles'      => ['css/calendar.css'],
            'scripts'     => ['js/calendar.js'],
        ]);
    }
}

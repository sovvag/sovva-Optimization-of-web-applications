<?php
class HomeController extends Controller
{
    // Главная страница
    public function index()
    {
        $doctorModel = new Doctor();
        $doctors = $doctorModel->allActive();

        $this->view('home/index', [
            'title'       => 'Офтальмологическая клиника: запись к врачу онлайн',
            'description' => 'Запишитесь онлайн к офтальмологу, офтальмохирургу или ретинологу. '
                           . 'Диагностика зрения, лечение глаз у взрослых и детей.',
            'doctorCount' => count($doctors),
        ]);
    }

    // Временная страница (пока для ролей без своих рабочих страниц)
    public function dashboard()
    {
        if (Auth::can('schedule.view') || Auth::can('roles.manage')) {
            redirect(Auth::homeUrl());
        }
        $this->view('home/dashboard', [
            'title'       => 'Личный кабинет',
            'noindex'     => true,
            'permissions' => Auth::permissions(),
        ]);
    }
    
        public function prices()
    {
        $this->view('home/prices', [
            'title'       => 'Цены на приём офтальмолога',
            'description' => 'Стоимость приёма врачей клиники: офтальмолог, офтальмохирург, ретинолог.',
            'doctors'     => (new Doctor())->allActive(),
        ]);
    }

    public function contacts()
    {
        $this->view('home/contacts', [
            'title'       => 'Контакты клиники',
            'description' => 'Адрес, телефон и режим работы офтальмологической клиники.',
        ]);
    }
}

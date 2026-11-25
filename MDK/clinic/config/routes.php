<?php
// ============================================================
// routes.php: таблица маршрутов (какой адрес -> какой метод)
//
// $router->get('/адрес', 'Контроллер@метод', [настройки]);
// Настройки:
//   'auth' => true            нужен вход в аккаунт
//   'perm' => 'код.права'     нужно конкретное право (см. таблицу permissions)
//                             (право включает и проверку входа в аккаунт)
// В адресе {slug} или {id} - это переменная часть.
//
// Адреса /schedule и /patients общие для администратора и руководителя:
// доступ решает не название роли, а права из базы данных.
// ============================================================

// --- Публичные страницы ---
$router->get('/',                'HomeController@index');
$router->get('/sitemap.xml',     'SitemapController@index');
$router->get('/doctors',         'DoctorController@index');
$router->get('/doctors/{slug}',  'DoctorController@show');
$router->get('/articles',        'ArticleController@index');
$router->get('/articles/{slug}', 'ArticleController@show');
$router->get('/prices',   'HomeController@prices');
$router->get('/contacts', 'HomeController@contacts');

// --- Вход, регистрация, выход ---
$router->get('/login',     'AuthController@showLogin');
$router->post('/login',    'AuthController@login');
$router->get('/register',  'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout',   'AuthController@logout', ['auth' => true]);

// --- Временная страница ---
$router->get('/dashboard', 'HomeController@dashboard', ['auth' => true]);

// --- Пациент: запись на приём ---
$router->get('/api/slots',                    'AppointmentController@slots',  ['perm' => 'appointment.create']);
$router->post('/appointments',                'AppointmentController@create', ['perm' => 'appointment.create']);
$router->get('/my-appointments',              'AppointmentController@mine',   ['perm' => 'appointment.view_own']);
$router->post('/my-appointments/{id}/cancel', 'AppointmentController@cancel', ['perm' => 'appointment.view_own']);

// --- Администратор и руководитель: расписание ---
$router->get('/schedule',                           'AdminScheduleController@index',        ['perm' => 'schedule.view']);
$router->post('/schedule/appointments/{id}/status', 'AdminScheduleController@updateStatus', ['perm' => 'appointment.approve']);
$router->post('/schedule/appointments',             'AdminScheduleController@store',        ['perm' => 'appointment.manage']);
$router->post('/schedule/appointments/{id}/move',   'AdminScheduleController@move',         ['perm' => 'appointment.manage']);
$router->post('/schedule/appointments/{id}/update', 'AdminScheduleController@update',       ['perm' => 'appointment.manage']);
$router->post('/schedule/appointments/{id}/delete', 'AdminScheduleController@delete',       ['perm' => 'appointment.manage']);
$router->get('/api/patients/search',                'PatientController@search',             ['perm' => 'appointment.manage']);

// --- Администратор и руководитель: картотека пациентов ---
$router->get('/patients',               'PatientController@index',  ['perm' => 'patients.manage']);
$router->get('/patients/create',        'PatientController@create', ['perm' => 'patients.manage']);
$router->post('/patients/create',       'PatientController@store',  ['perm' => 'patients.manage']);
$router->get('/patients/{id}/edit',     'PatientController@edit',   ['perm' => 'patients.manage']);
$router->post('/patients/{id}/edit',    'PatientController@update', ['perm' => 'patients.manage']);
$router->post('/patients/{id}/delete',  'PatientController@delete', ['perm' => 'patients.manage']);

// --- Уведомления администратора (запрашиваются через JavaScript) ---
$router->get('/api/notifications',                'NotificationController@unread', ['perm' => 'appointment.approve']);
$router->post('/api/notifications/{id}/read',     'NotificationController@read',   ['perm' => 'appointment.approve']);

// --- Профиль (доступен всем вошедшим пользователям) ---
$router->get('/profile',           'ProfileController@edit',     ['perm' => 'profile.edit']);
$router->post('/profile',          'ProfileController@update',   ['perm' => 'profile.edit']);
$router->post('/profile/password', 'ProfileController@password', ['perm' => 'profile.edit']);

// --- Руководитель: врачи, график работы, отчёты ---
$router->get('/manager/doctors',                'DoctorManageController@index',  ['perm' => 'doctors.manage']);
$router->get('/manager/doctors/create',         'DoctorManageController@create', ['perm' => 'doctors.manage']);
$router->post('/manager/doctors/create',        'DoctorManageController@store',  ['perm' => 'doctors.manage']);
$router->get('/manager/doctors/{id}/edit',      'DoctorManageController@edit',   ['perm' => 'doctors.manage']);
$router->post('/manager/doctors/{id}/edit',     'DoctorManageController@update', ['perm' => 'doctors.manage']);

$router->get('/manager/work-schedule',               'ScheduleController@index',  ['perm' => 'work_schedule.manage']);
$router->post('/manager/work-schedule',              'ScheduleController@store',  ['perm' => 'work_schedule.manage']);
$router->post('/manager/work-schedule/{id}/update',  'ScheduleController@update', ['perm' => 'work_schedule.manage']);
$router->post('/manager/work-schedule/{id}/delete',  'ScheduleController@delete', ['perm' => 'work_schedule.manage']);

$router->get('/manager/reports', 'ReportController@index', ['perm' => 'reports.view']);

// --- Системный администратор ---
$router->get('/sysadmin/roles',               'SysAdminController@roles',      ['perm' => 'roles.manage']);
$router->post('/sysadmin/roles',              'SysAdminController@saveRoles',  ['perm' => 'roles.manage']);
$router->get('/sysadmin/users',               'SysAdminController@users',      ['perm' => 'users.manage']);
$router->get('/sysadmin/users/create',        'SysAdminController@createUser', ['perm' => 'users.manage']);
$router->post('/sysadmin/users/create',       'SysAdminController@storeUser',  ['perm' => 'users.manage']);
$router->get('/sysadmin/users/{id}/edit',     'SysAdminController@editUser',   ['perm' => 'users.manage']);
$router->post('/sysadmin/users/{id}/edit',    'SysAdminController@updateUser', ['perm' => 'users.manage']);
$router->get('/sysadmin/audit',               'SysAdminController@audit',      ['perm' => 'users.manage']);

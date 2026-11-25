<?php
// Управление врачами (руководитель): список, добавление, изменение, фото.
// Страница врача для пациентов создаётся автоматически по шаблону doctors/show.php.
class DoctorManageController extends Controller
{
    private $categories = ['adults', 'children', 'children14_adults'];

    // GET /manager/doctors
    public function index()
    {
        $this->view('manager/doctors', [
            'title'   => 'Врачи',
            'noindex' => true,
            'doctors' => (new Doctor())->allForManage(),
        ]);
    }

    // GET /manager/doctors/create
    public function create()
    {
        $this->form('Новый врач', null, '/manager/doctors/create');
    }

    // GET /manager/doctors/{id}/edit
    public function edit($id)
    {
        $doctor = (new Doctor())->findAny($id);
        if (!$doctor) {
            $this->notFound();
        }
        $this->form('Карточка врача', $doctor, '/manager/doctors/' . (int)$id . '/edit');
    }

    // POST /manager/doctors/create
    public function store()
    {
        $errors = [];
        $data = $this->collect($errors, null);
        $photo = $this->uploadPhoto($errors);

        if (!empty($errors)) {
            $this->back($data, $errors, '/manager/doctors/create');
        }

        $doctorModel = new Doctor();
        $id = $doctorModel->save($data);
        if ($photo !== null) {
            $doctorModel->setPhoto($id, $photo);
        }
        (new AuditLog())->add(Auth::id(), 'doctor.create', 'doctor', $id, 'Добавлен врач');
        flash('success', 'Врач добавлен. Его страница на сайте уже создана.');
        redirect('/manager/doctors');
    }

    // POST /manager/doctors/{id}/edit
    public function update($id)
    {
        $doctorModel = new Doctor();
        $doctor = $doctorModel->findAny($id);
        if (!$doctor) {
            $this->notFound();
        }

        $errors = [];
        $data = $this->collect($errors, $doctor);
        $photo = $this->uploadPhoto($errors);

        if (!empty($errors)) {
            $this->back($data, $errors, '/manager/doctors/' . (int)$id . '/edit');
        }

        $doctorModel->save($data, $id);
        if ($photo !== null) {
            $doctorModel->setPhoto($id, $photo);
            FileUploader::delete($doctor['photo_path']);
        } elseif (isset($_POST['remove_photo']) && !empty($doctor['photo_path'])) {
            $doctorModel->setPhoto($id, null);
            FileUploader::delete($doctor['photo_path']);
        }
        (new AuditLog())->add(Auth::id(), 'doctor.update', 'doctor', (int)$id, 'Изменены данные врача');
        flash('success', 'Данные врача сохранены.');
        redirect('/manager/doctors');
    }

    // ---------- Вспомогательное ----------

    private function form($title, $doctor, $action)
    {
        $this->view('manager/doctor_form', [
            'title'       => $title,
            'noindex'     => true,
            'doctor'      => $doctor,
            'formAction'  => $action,
            'specialties' => (new Doctor())->specialties(),
            'scripts'     => ['js/profile.js'],
        ]);
    }

    private function back($data, $errors, $path)
    {
        $_SESSION['old'] = $data;
        $_SESSION['errors'] = $errors;
        redirect($path);
    }

    // Фото врача: 3:4, 600x800 (через FileUploader, с проверкой содержимого)
    private function uploadPhoto(&$errors)
    {
        if (!empty($errors) || !isset($_FILES['photo'])) {
            return null;
        }
        try {
            return FileUploader::saveImage($_FILES['photo'], 'doctors', 600, 800);
        } catch (RuntimeException $e) {
            $errors['photo'] = $e->getMessage();
            return null;
        }
    }

    private function collect(&$errors, $existing)
    {
        $data = [
            'last_name'        => post_str('last_name'),
            'first_name'       => post_str('first_name'),
            'middle_name'      => post_str('middle_name'),
            'specialty_id'     => post_str('specialty_id'),
            'experience_years' => post_str('experience_years'),
            'patient_category' => post_str('patient_category'),
            'skills'           => post_str('skills'),
            'price'            => str_replace(',', '.', post_str('price')),
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        ];

        if (!Validator::name($data['last_name'])) {
            $errors['last_name'] = 'Введите фамилию (только буквы)';
        }
        if (!Validator::name($data['first_name'])) {
            $errors['first_name'] = 'Введите имя (только буквы)';
        }
        if ($data['middle_name'] !== '' && !Validator::name($data['middle_name'])) {
            $errors['middle_name'] = 'Отчество: только буквы';
        }
        if (!ctype_digit($data['specialty_id']) || !(new Doctor())->specialtyExists($data['specialty_id'])) {
            $errors['specialty_id'] = 'Выберите должность из списка';
        }
        if (!ctype_digit($data['experience_years']) || (int)$data['experience_years'] > 70) {
            $errors['experience_years'] = 'Стаж: число от 0 до 70';
        }
        if (!in_array($data['patient_category'], $this->categories, true)) {
            $errors['patient_category'] = 'Выберите, кого принимает врач';
        }
        if (!is_numeric($data['price']) || $data['price'] < 0 || $data['price'] > 1000000) {
            $errors['price'] = 'Стоимость приёма: число от 0 до 1 000 000';
        } else {
            $data['price'] = number_format((float)$data['price'], 2, '.', '');
        }
        if (mb_strlen($data['skills']) > 1000) {
            $errors['skills'] = 'Навыки: не длиннее 1000 символов';
        }

        // Нельзя отключить врача, у которого есть будущие записи
        if ($existing && (int)$existing['is_active'] === 1 && $data['is_active'] === 0
            && (new Doctor())->futureAppointments($existing['id']) > 0) {
            $errors['is_active'] = 'У врача есть будущие записи. Сначала отмените или перенесите их.';
        }
        return $data;
    }
}

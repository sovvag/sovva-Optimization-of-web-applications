<?php
// Картотека пациентов (администратор и руководитель)
class PatientController extends Controller
{
    // GET /patients
    public function index()
    {
        $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';

        $this->view('admin/patients', [
            'title'   => 'Картотека пациентов',
            'noindex' => true,
            'patients' => (new Patient())->all($q),
            'query'   => $q,
            'scripts' => ['js/patients.js'],
        ]);
    }

    // GET /api/patients/search?q=иванов  (поиск пациента в окне "Запись")
    public function search()
    {
        $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $items = [];
        if (mb_strlen($q) >= 2) {
            foreach (array_slice((new Patient())->all($q), 0, 8) as $p) {
                $items[] = [
                    'id'    => (int)$p['id'],
                    'name'  => full_name($p),
                    'phone' => $p['phone'] ? $p['phone'] : '',
                ];
            }
        }
        $this->json(['success' => true, 'items' => $items]);
    }

    // GET /patients/create
    public function create()
    {
        $this->view('admin/patient_form', [
            'title'      => 'Новый пациент',
            'noindex'    => true,
            'patient'    => null,
            'formAction' => '/patients/create',
        ]);
    }

    // POST /patients/create
    public function store()
    {
        $errors = [];
        $data = $this->collect($errors);

        if (!empty($errors)) {
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $errors;
            redirect('/patients/create');
        }

        $id = (new Patient())->save($data);
        (new AuditLog())->add(Auth::id(), 'patient.create', 'patient', $id, 'Добавлена карточка пациента');
        flash('success', 'Карточка пациента создана.');
        redirect('/patients');
    }

    // GET /patients/{id}/edit
    public function edit($id)
    {
        $patient = (new Patient())->find($id);
        if (!$patient) {
            $this->notFound();
        }
        $this->view('admin/patient_form', [
            'title'      => 'Карточка пациента',
            'noindex'    => true,
            'patient'    => $patient,
            'formAction' => '/patients/' . (int)$id . '/edit',
        ]);
    }

    // POST /patients/{id}/edit
    public function update($id)
    {
        $patientModel = new Patient();
        if (!$patientModel->find($id)) {
            $this->notFound();
        }

        $errors = [];
        $data = $this->collect($errors);

        if (!empty($errors)) {
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $errors;
            redirect('/patients/' . (int)$id . '/edit');
        }

        $patientModel->save($data, $id);
        (new AuditLog())->add(Auth::id(), 'patient.update', 'patient', (int)$id, 'Изменена карточка пациента');
        flash('success', 'Изменения сохранены.');
        redirect('/patients');
    }

    // POST /patients/{id}/delete
    // Окно "Пациент подписал отказ?" показывает JavaScript. Кнопка "Да" отправляет refusal_signed=1.
    // Сервер проверяет это сам: подмена запроса без этого поля удаление не выполнит.
    public function delete($id)
    {
        if (post_str('refusal_signed') !== '1') {
            flash('error', 'Удалить карту можно только после подписанного отказа пациента от обработки персональных данных.');
            redirect('/patients');
        }

        $result = (new Patient())->deleteCard((int)$id);
        if ($result === null) {
            $this->notFound();
        }

        (new AuditLog())->add(Auth::id(), 'patient.delete', 'patient', (int)$id, 'Отказ от ПД подписан. Результат: ' . $result);

        if ($result === 'anonymized') {
            flash('success', 'Персональные данные удалены. Карточка обезличена, потому что есть завершённые приёмы (нужны для отчётности).');
        } else {
            flash('success', 'Карточка пациента удалена.');
        }
        redirect('/patients');
    }

    // Берём данные из формы и проверяем. Ошибки складываем в $errors.
    private function collect(&$errors)
    {
        $data = [
            'last_name'   => post_str('last_name'),
            'first_name'  => post_str('first_name'),
            'middle_name' => post_str('middle_name'),
            'birth_date'  => post_str('birth_date'),
            'phone'       => post_str('phone'),
            'address'     => post_str('address'),
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
        if ($data['birth_date'] !== '' && !Validator::birthDate($data['birth_date'])) {
            $errors['birth_date'] = 'Проверьте дату рождения';
        }
        if ($data['phone'] !== '' && !Validator::phone($data['phone'])) {
            $errors['phone'] = 'Телефон: цифры, пробелы, скобки и дефис (6-20 символов)';
        }
        if (mb_strlen($data['address']) > 255) {
            $errors['address'] = 'Адрес не длиннее 255 символов';
        }
        return $data;
    }
}

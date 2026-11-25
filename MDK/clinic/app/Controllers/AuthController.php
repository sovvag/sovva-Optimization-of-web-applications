<?php
class AuthController extends Controller
{
    // ---------- Вход ----------

    public function showLogin()
    {
        if (Auth::check()) {
            redirect(Auth::homeUrl());
        }
        $this->view('auth/login', [
            'title'   => 'Вход в личный кабинет',
            'noindex' => true,
        ], 'auth');
    }

    public function login()
    {
        $email    = post_str('email');
        $password = post_str('password', false);
        $ip       = $_SERVER['REMOTE_ADDR'];

        $limiter = new RateLimiter();

        // 1. Слишком много неудачных попыток?
        if ($limiter->isBlocked($ip)) {
            flash('error', 'Слишком много неудачных попыток входа. Подождите ' . LOGIN_BLOCK_MINUTES . ' минут.');
            redirect('/login');
        }

        // 2. Ищем пользователя и проверяем пароль
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Если пользователя нет, всё равно проверяем пароль по "пустышке",
        // чтобы время ответа не выдавало, существует такой email или нет.
        $dummyHash = '$2y$10$XqL/0XU6pssj4B0Rn1Hgw.PZVusN3LioQ0KQZLrUcdrff9.5wLpJC';
        $hash = $user ? $user['password_hash'] : $dummyHash;
        $passwordOk = password_verify($password, $hash);

        if ($user && $user['is_active'] && $passwordOk) {
            $limiter->clear($ip);
            Auth::login($user['id']);
            redirect(Auth::homeUrl());
        }

        // 3. Ошибка: одно и то же сообщение для любой причины
        $limiter->addFailure($ip, $email);
        $_SESSION['old'] = ['email' => $email];
        $_SESSION['errors'] = ['form' => 'Неверный email или пароль'];
        redirect('/login');
    }

    // ---------- Регистрация ----------

    public function showRegister()
    {
        if (Auth::check()) {
            redirect(Auth::homeUrl());
        }
        $this->view('auth/register', [
            'title'   => 'Регистрация пациента',
            'noindex' => true,
        ], 'auth');
    }

    public function register()
    {
        // Берём ТОЛЬКО нужные поля. Поля "role" в форме нет и быть не должно.
        $data = [
            'last_name'   => post_str('last_name'),
            'first_name'  => post_str('first_name'),
            'middle_name' => post_str('middle_name'),
            'birth_date'  => post_str('birth_date'),
            'email'       => post_str('email'),
            'password'    => post_str('password', false),
        ];
        $password2 = post_str('password2', false);
        $consent   = isset($_POST['consent']);

        $errors = [];

        if (!Validator::name($data['last_name'])) {
            $errors['last_name'] = 'Введите фамилию (только буквы)';
        }
        if (!Validator::name($data['first_name'])) {
            $errors['first_name'] = 'Введите имя (только буквы)';
        }
        if ($data['middle_name'] !== '' && !Validator::name($data['middle_name'])) {
            $errors['middle_name'] = 'Отчество: только буквы';
        }
        if (!Validator::birthDate($data['birth_date'])) {
            $errors['birth_date'] = 'Укажите корректную дату рождения';
        }
        if (!Validator::email($data['email'])) {
            $errors['email'] = 'Введите корректный email, например name@mail.ru';
        }

        $passError = Validator::password($data['password']);
        if ($passError !== '') {
            $errors['password'] = $passError;
        } elseif ($data['password'] !== $password2) {
            $errors['password2'] = 'Пароли не совпадают';
        }

        if (!$consent) {
            $errors['consent'] = 'Нужно согласие на обработку персональных данных';
        }

        // Email уже занят?
        $userModel = new User();
        if (!isset($errors['email']) && $userModel->emailExists($data['email'])) {
            $errors['email'] = 'Пользователь с таким email уже зарегистрирован';
        }

        if (!empty($errors)) {
            unset($data['password']);              // пароль обратно в форму не возвращаем
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $errors;
            redirect('/register');
        }

        try {
            $userId = $userModel->registerPatient($data);
        } catch (PDOException $ex) {
            // 23000 = нарушение уникальности (кто-то успел занять email)
            if ($ex->getCode() == '23000') {
                $_SESSION['errors'] = ['email' => 'Пользователь с таким email уже зарегистрирован'];
                redirect('/register');
            }
            throw $ex;
        }

        Auth::login($userId);
        $_SESSION['goal'] = 'register_success';
        flash('success', 'Регистрация прошла успешно. Добро пожаловать!');
        redirect('/doctors');
    }

    // ---------- Выход ----------

    public function logout()
    {
        Auth::logout();
        session_start();
        flash('success', 'Вы вышли из аккаунта.');
        redirect('/login');
    }
}

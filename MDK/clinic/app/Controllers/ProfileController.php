<?php
// Профиль: имя, дата рождения, аватарка, смена пароля. Доступен всем ролям.
// ВАЖНО: роль здесь только показывается. Менять роль может ТОЛЬКО системный
// администратор. Если дать пользователю менять роль себе, это будет
// вертикальное повышение прав.
class ProfileController extends Controller
{
    // GET /profile
    public function edit()
    {
        $this->view('profile/edit', [
            'title'   => 'Мой профиль',
            'noindex' => true,
            'user'    => Auth::user(),
            'scripts' => ['js/profile.js'],
        ]);
    }

    // POST /profile  (имя, дата рождения, фото)
    public function update()
    {
        $user = Auth::user();

        $data = [
            'last_name'   => post_str('last_name'),
            'first_name'  => post_str('first_name'),
            'middle_name' => post_str('middle_name'),
            'birth_date'  => post_str('birth_date'),
        ];

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
        if ($data['birth_date'] !== '' && !Validator::birthDate($data['birth_date'])) {
            $errors['birth_date'] = 'Проверьте дату рождения';
        }

        // Фото: сначала проверяем и сохраняем новое, и только потом удаляем старое
        $newAvatar = null;
        $removeAvatar = isset($_POST['remove_avatar']);
        if (empty($errors) && isset($_FILES['avatar'])) {
            try {
                $newAvatar = FileUploader::saveImage($_FILES['avatar'], 'avatars', 400, 400);
            } catch (RuntimeException $e) {
                $errors['avatar'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $errors;
            redirect('/profile');
        }

        $userModel = new User();
        $userModel->updateProfile($user['id'], $data, $user['role_name'] === 'patient');

        if ($newAvatar !== null) {
            $userModel->setAvatar($user['id'], $newAvatar);
            FileUploader::delete($user['avatar_path']);
        } elseif ($removeAvatar && !empty($user['avatar_path'])) {
            $userModel->setAvatar($user['id'], null);
            FileUploader::delete($user['avatar_path']);
        }

        (new AuditLog())->add($user['id'], 'profile.update', 'user', (int)$user['id'], 'Изменён профиль');
        flash('success', 'Профиль сохранён.');
        redirect('/profile');
    }

    // POST /profile/password
    public function password()
    {
        $user = Auth::user();
        $current = post_str('current_password', false);
        $new     = post_str('new_password', false);
        $new2    = post_str('new_password2', false);

        $error = '';
        if (!password_verify($current, $user['password_hash'])) {
            $error = 'Текущий пароль указан неверно.';
        } elseif (Validator::password($new) !== '') {
            $error = Validator::password($new);
        } elseif ($new !== $new2) {
            $error = 'Новые пароли не совпадают.';
        }

        if ($error !== '') {
            $_SESSION['errors'] = ['password' => $error];
            redirect('/profile');
        }

        (new User())->setPassword($user['id'], $new);
        session_regenerate_id(true);         // после смены пароля выдаём новый ID сессии
        (new AuditLog())->add($user['id'], 'profile.password', 'user', (int)$user['id'], 'Изменён пароль');
        flash('success', 'Пароль изменён.');
        redirect('/profile');
    }
}

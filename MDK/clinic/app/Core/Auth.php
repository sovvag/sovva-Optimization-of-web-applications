<?php
// ============================================================
// Auth.php: вход, выход, текущий пользователь и его права.
// В сессии храним ТОЛЬКО id пользователя. Роль и права каждый раз
// берём из БД - так нельзя подделать роль и сисадмин может
// изменить права, не дожидаясь повторного входа пользователя.
// ============================================================
class Auth
{
    private static $user = null;
    private static $permissions = null;

    // Вход пользователя
    public static function login($userId)
    {
        session_regenerate_id(true);    // новый ID сессии (защита от фиксации сессии)
        $_SESSION['user_id'] = (int)$userId;
        $_SESSION['last_activity'] = time();
    }

    // Выход
    public static function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$user = null;
        self::$permissions = null;
    }

    // Автоматический выход после долгого бездействия
    public static function checkTimeout()
    {
        if (isset($_SESSION['user_id'])) {
            $last = isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 0;
            if (time() - $last > SESSION_LIFETIME) {
                self::logout();
                session_start();
                flash('error', 'Сессия завершена из-за бездействия. Войдите снова.');
            } else {
                $_SESSION['last_activity'] = time();
            }
        }
    }

    // Текущий пользователь (массив из БД) или null
    public static function user()
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            $userModel = new User();
            self::$user = $userModel->findById($_SESSION['user_id']);
            if (self::$user === null) {
                unset($_SESSION['user_id']);    // пользователь удалён или заблокирован
            }
        }
        return self::$user;
    }

    public static function check()
    {
        return self::user() !== null;
    }

    public static function id()
    {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }

    // Код роли: patient, administrator, manager, sysadmin
    public static function role()
    {
        $user = self::user();
        return $user ? $user['role_name'] : null;
    }

    // Есть ли у пользователя право: Auth::can('patients.manage')
    public static function can($code)
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if (self::$permissions === null) {
            $permModel = new Permission();
            self::$permissions = $permModel->codesForRole($user['role_id']);
        }
        return in_array($code, self::$permissions, true);
    }

    // Список всех прав текущего пользователя
    public static function permissions()
    {
        if (self::check()) {
            self::can('profile.edit');    // заполнит кэш прав
        }
        return self::$permissions === null ? [] : self::$permissions;
    }

    // Куда отправить пользователя после входа
    public static function homeUrl()
    {
        if (self::can('schedule.view')) {
            return '/schedule';      // администратор и руководитель: таблица расписания
        }
        if (self::can('roles.manage')) {
            return '/sysadmin/roles';    // системный администратор: права ролей
        }
        if (self::role() === 'patient') {
            return '/doctors';
        }
        return '/dashboard';         // остальные (пока временная страница)
    }
}

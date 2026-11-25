<?php
// Панель системного администратора: права ролей, пользователи, журнал действий.
class SysAdminController extends Controller
{
    // Эти права нельзя забрать у системного администратора (иначе он потеряет доступ к панели)
    private $lockedForSysadmin = ['roles.manage', 'users.manage', 'profile.edit'];

    // Эти права принадлежат ТОЛЬКО системному администратору. Выдать их другой роли нельзя:
    // иначе та роль смогла бы сама дать себе любые права (вертикальное повышение прав).
    private $systemOnly = ['roles.manage', 'users.manage'];

    // ============================================================
    // Права ролей
    // ============================================================

    // GET /sysadmin/roles
    public function roles()
    {
        $this->view('sysadmin/roles', [
            'title'          => 'Права ролей',
            'noindex'        => true,
            'roles'          => (new Role())->all(),
            'permissions'    => (new Permission())->all(),
            'granted'        => (new Permission())->granted(),
            'locked'         => $this->lockedForSysadmin,
            'systemOnly'     => $this->systemOnly,
        ]);
    }

    // POST /sysadmin/roles
    public function saveRoles()
    {
        $roles = (new Role())->all();
        $permModel = new Permission();
        $permissions = $permModel->all();

        $codeById = [];
        foreach ($permissions as $p) {
            $codeById[(int)$p['id']] = $p['code'];
        }
        $sysadminId = 0;
        $roleNames = [];
        foreach ($roles as $r) {
            $roleNames[(int)$r['id']] = $r['title'];
            if ($r['name'] === 'sysadmin') {
                $sysadminId = (int)$r['id'];
            }
        }

        // 1. Разбираем отправленную матрицу: берём только существующие роли и права
        $posted = (isset($_POST['perm']) && is_array($_POST['perm'])) ? $_POST['perm'] : [];
        $map = [];
        foreach ($roles as $r) {
            $roleId = (int)$r['id'];
            $ids = [];
            if (isset($posted[$roleId]) && is_array($posted[$roleId])) {
                foreach ($posted[$roleId] as $permId) {
                    $permId = (int)$permId;
                    if (!isset($codeById[$permId])) {
                        continue;                           // такого права нет
                    }
                    if (in_array($codeById[$permId], $this->systemOnly, true) && $roleId !== $sysadminId) {
                        continue;                           // системные права только у сисадмина
                    }
                    $ids[$permId] = $permId;
                }
            }
            // 2. Сисадмин всегда сохраняет обязательные права
            if ($roleId === $sysadminId) {
                foreach ($codeById as $permId => $code) {
                    if (in_array($code, $this->lockedForSysadmin, true)) {
                        $ids[$permId] = $permId;
                    }
                }
            }
            $map[$roleId] = array_values($ids);
        }

        // 3. Что изменилось (для журнала)
        $old = $permModel->granted();
        $changes = [];
        foreach ($map as $roleId => $ids) {
            $new = array_flip($ids);
            foreach ($codeById as $permId => $code) {
                $was = isset($old[$roleId . '-' . $permId]);
                $now = isset($new[$permId]);
                if ($was && !$now) {
                    $changes[] = $roleNames[$roleId] . ': -' . $code;
                } elseif (!$was && $now) {
                    $changes[] = $roleNames[$roleId] . ': +' . $code;
                }
            }
        }

        $permModel->replaceAll($map);

        if (empty($changes)) {
            flash('success', 'Изменений нет.');
        } else {
            (new AuditLog())->add(Auth::id(), 'roles.update', 'role', null, mb_substr(implode('; ', $changes), 0, 255));
            flash('success', 'Права сохранены (изменений: ' . count($changes) . '). Они действуют сразу, повторный вход не нужен.');
        }
        redirect('/sysadmin/roles');
    }

    // ============================================================
    // Пользователи
    // ============================================================

    // GET /sysadmin/users
    public function users()
    {
        $q = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
        $roleId = isset($_GET['role']) ? (int)$_GET['role'] : 0;

        $this->view('sysadmin/users', [
            'title'   => 'Аккаунты',
            'noindex' => true,
            'users'   => (new User())->search($q, $roleId),
            'roles'   => (new Role())->all(),
            'query'   => $q,
            'roleId'  => $roleId,
        ]);
    }

    // GET /sysadmin/users/create
    public function createUser()
    {
        $this->userForm('Новый пользователь', null, '/sysadmin/users/create');
    }

    // POST /sysadmin/users/create
    public function storeUser()
    {
        $errors = [];
        $data = $this->collect($errors, null);
        $role = (new Role())->find($data['role_id']);

        if (!empty($errors) || !$role) {
            $this->back($data, $errors, '/sysadmin/users/create');
        }

        $id = (new User())->createByAdmin($data, $role);
        (new AuditLog())->add(Auth::id(), 'user.create', 'user', $id, 'Создан аккаунт: ' . $role['title']);
        flash('success', 'Пользователь создан. Передайте ему временный пароль.');
        redirect('/sysadmin/users');
    }

    // GET /sysadmin/users/{id}/edit
    public function editUser($id)
    {
        $user = (new User())->findAny($id);
        if (!$user) {
            $this->notFound();
        }
        $this->userForm('Пользователь', $user, '/sysadmin/users/' . (int)$id . '/edit');
    }

    // POST /sysadmin/users/{id}/edit
    public function updateUser($id)
    {
        $userModel = new User();
        $user = $userModel->findAny($id);
        if (!$user) {
            $this->notFound();
        }
        $isSelf = ((int)$user['id'] === (int)Auth::id());

        $errors = [];
        $data = $this->collect($errors, $user);

        // Свою роль и активность менять нельзя (защита от самоблокировки)
        if ($isSelf) {
            $data['role_id'] = (int)$user['role_id'];
            $data['is_active'] = 1;
            unset($errors['role_id']);
        }
        $role = (new Role())->find($data['role_id']);
        if (!$role) {
            $errors['role_id'] = 'Выберите роль из списка';
        }

        if (!empty($errors)) {
            $this->back($data, $errors, '/sysadmin/users/' . (int)$id . '/edit');
        }

        try {
            $before = $userModel->updateByAdmin($id, $data, $role);
        } catch (DomainException $e) {
            $this->back($data, ['form' => $e->getMessage()], '/sysadmin/users/' . (int)$id . '/edit');
        }

        $log = new AuditLog();
        if ($before['role_name'] !== $role['name']) {
            $log->add(Auth::id(), 'user.role_change', 'user', (int)$id, 'Роль: ' . $before['role_name'] . ' -> ' . $role['name']);
        }
        if ((int)$before['is_active'] !== (int)$data['is_active']) {
            $log->add(Auth::id(), $data['is_active'] ? 'user.activate' : 'user.deactivate', 'user', (int)$id, null);
        }

        // Новый пароль (если ввели)
        if ($data['password'] !== '') {
            $userModel->setPassword($id, $data['password']);
            $log->add(Auth::id(), 'user.password_reset', 'user', (int)$id, 'Пароль сброшен администратором');
        }
        $log->add(Auth::id(), 'user.update', 'user', (int)$id, 'Изменены данные пользователя');

        flash('success', 'Данные пользователя сохранены.');
        redirect('/sysadmin/users');
    }

    // ============================================================
    // Журнал действий
    // ============================================================

    // GET /sysadmin/audit
    public function audit()
    {
        $this->view('sysadmin/audit', [
            'title'   => 'Журнал действий',
            'noindex' => true,
            'entries' => (new AuditLog())->latest(200),
        ]);
    }

    // ---------- Вспомогательное ----------

    private function userForm($title, $user, $action)
    {
        $this->view('sysadmin/user_form', [
            'title'      => $title,
            'noindex'    => true,
            'target'     => $user,
            'formAction' => $action,
            'roles'      => (new Role())->all(),
            'isSelf'     => $user && (int)$user['id'] === (int)Auth::id(),
        ]);
    }

    private function back($data, $errors, $path)
    {
        unset($data['password']);               // пароль обратно в форму не возвращаем
        $_SESSION['old'] = $data;
        $_SESSION['errors'] = $errors;
        redirect($path);
    }

    // Читаем и проверяем форму пользователя. $existing = null при создании.
    private function collect(&$errors, $existing)
    {
        $data = [
            'last_name'   => post_str('last_name'),
            'first_name'  => post_str('first_name'),
            'middle_name' => post_str('middle_name'),
            'birth_date'  => post_str('birth_date'),
            'email'       => post_str('email'),
            'password'    => post_str('password', false),
            'role_id'     => (int)post_str('role_id'),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];
        $userModel = new User();
        $existingId = $existing ? (int)$existing['id'] : 0;

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
        if (!Validator::email($data['email'])) {
            $errors['email'] = 'Введите корректный email';
        } elseif ($userModel->emailTaken($data['email'], $existingId)) {
            $errors['email'] = 'Этот email уже используется';
        }
        if (!(new Role())->find($data['role_id'])) {
            $errors['role_id'] = 'Выберите роль из списка';
        }

        // Пароль: при создании обязателен, при изменении только если хотят сменить
        if ($existing === null || $data['password'] !== '') {
            $passError = Validator::password($data['password']);
            if ($passError !== '') {
                $errors['password'] = $passError;
            }
        }
        return $data;
    }
}

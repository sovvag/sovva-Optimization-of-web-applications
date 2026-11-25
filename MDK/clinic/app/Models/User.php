<?php
class User extends Model
{
    // Активный пользователь по id (вместе с названием роли)
    public function findById($id)
    {
        return $this->fetchOne(
            'SELECT u.*, r.name AS role_name, r.title AS role_title
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.is_active = 1',
            [$id]
        );
    }

    // Пользователь по email (для входа)
    public function findByEmail($email)
    {
        return $this->fetchOne(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?',
            [$email]
        );
    }

    public function emailExists($email)
    {
        return $this->fetchOne('SELECT id FROM users WHERE email = ?', [$email]) !== null;
    }

    // Регистрация пациента: создаём аккаунт И карточку пациента.
    // Роль ВСЕГДА patient. Значение из формы не используется вообще.
    public function registerPatient($data)
    {
        $roleModel = new Role();
        $roleId = $roleModel->idByName('patient');

        $this->db->beginTransaction();   // обе записи создаются вместе или не создаются совсем
        try {
            $this->run(
                'INSERT INTO users (email, password_hash, role_id, last_name, first_name, middle_name, birth_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $roleId,
                    $data['last_name'],
                    $data['first_name'],
                    $data['middle_name'] !== '' ? $data['middle_name'] : null,
                    $data['birth_date'],
                ]
            );
            $userId = (int)$this->db->lastInsertId();

            $this->run(
                'INSERT INTO patients (user_id, last_name, first_name, middle_name, birth_date)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $userId,
                    $data['last_name'],
                    $data['first_name'],
                    $data['middle_name'] !== '' ? $data['middle_name'] : null,
                    $data['birth_date'],
                ]
            );

            $this->db->commit();
            return $userId;
        } catch (Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    // Изменить ФИО и дату рождения. У пациента обновляем и карточку в картотеке.
    public function updateProfile($id, $data, $isPatient)
    {
        $middle = $data['middle_name'] !== '' ? $data['middle_name'] : null;
        $birth  = $data['birth_date'] !== '' ? $data['birth_date'] : null;

        $this->db->beginTransaction();
        try {
            $this->run(
                'UPDATE users SET last_name = ?, first_name = ?, middle_name = ?, birth_date = ? WHERE id = ?',
                [$data['last_name'], $data['first_name'], $middle, $birth, (int)$id]
            );
            if ($isPatient) {
                $this->run(
                    'UPDATE patients SET last_name = ?, first_name = ?, middle_name = ?, birth_date = ? WHERE user_id = ?',
                    [$data['last_name'], $data['first_name'], $middle, $birth, (int)$id]
                );
            }
            $this->db->commit();
        } catch (Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    // Записать путь к аватарке (null = удалить)
    public function setAvatar($id, $path)
    {
        $this->run('UPDATE users SET avatar_path = ? WHERE id = ?', [$path, (int)$id]);
    }

    public function setPassword($id, $newPassword)
    {
        $this->run('UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($newPassword, PASSWORD_DEFAULT), (int)$id]);
    }

    // ============================================================
    // Для системного администратора
    // ============================================================

    // Список пользователей с поиском и фильтром по роли
    public function search($query, $roleId)
    {
        $sql = 'SELECT u.id, u.email, u.last_name, u.first_name, u.middle_name, u.is_active, u.created_at,
                       r.title AS role_title, r.name AS role_name
                FROM users u JOIN roles r ON r.id = u.role_id WHERE 1 = 1';
        $params = [];
        if ($query !== '') {
            $safe = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
            $like = '%' . $safe . '%';
            $sql .= ' AND (u.email LIKE ? OR u.last_name LIKE ? OR u.first_name LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if ($roleId > 0) {
            $sql .= ' AND u.role_id = ?';
            $params[] = $roleId;
        }
        $sql .= ' ORDER BY u.last_name, u.first_name LIMIT 300';
        return $this->fetchAll($sql, $params);
    }

    // Пользователь по id, даже если он заблокирован
    public function findAny($id)
    {
        return $this->fetchOne(
            'SELECT u.*, r.name AS role_name, r.title AS role_title
             FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
            [(int)$id]
        );
    }

    // Email занят другим пользователем?
    public function emailTaken($email, $exceptId = 0)
    {
        return $this->fetchOne('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, (int)$exceptId]) !== null;
    }

    // Создать пользователя с любой ролью. Для роли patient создаётся и карточка пациента.
    public function createByAdmin($data, $role)
    {
        $middle = $data['middle_name'] !== '' ? $data['middle_name'] : null;
        $birth  = $data['birth_date'] !== '' ? $data['birth_date'] : null;

        $this->db->beginTransaction();
        try {
            $this->run(
                'INSERT INTO users (email, password_hash, role_id, last_name, first_name, middle_name, birth_date, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [$data['email'], password_hash($data['password'], PASSWORD_DEFAULT), (int)$role['id'],
                 $data['last_name'], $data['first_name'], $middle, $birth, (int)$data['is_active']]
            );
            $id = (int)$this->db->lastInsertId();
            if ($role['name'] === 'patient') {
                $this->ensurePatientCard($id);
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Изменить пользователя. Нельзя оставить систему без активного системного администратора.
    // Бросает DomainException с понятным текстом.
    public function updateByAdmin($id, $data, $role)
    {
        $middle = $data['middle_name'] !== '' ? $data['middle_name'] : null;
        $birth  = $data['birth_date'] !== '' ? $data['birth_date'] : null;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT u.id, u.is_active, r.name AS role_name
                 FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? FOR UPDATE'
            );
            $stmt->execute([(int)$id]);
            $current = $stmt->fetch();
            if (!$current) {
                throw new DomainException('Пользователь не найден.');
            }

            // Последний активный сисадмин: его нельзя ни понизить, ни заблокировать
            $losesAdmin = $current['role_name'] === 'sysadmin' && (int)$current['is_active'] === 1
                && ($role['name'] !== 'sysadmin' || (int)$data['is_active'] === 0);
            if ($losesAdmin) {
                $row = $this->fetchOne(
                    "SELECT COUNT(*) AS n FROM users u JOIN roles r ON r.id = u.role_id
                     WHERE r.name = 'sysadmin' AND u.is_active = 1 AND u.id <> ?",
                    [(int)$id]
                );
                if ((int)$row['n'] === 0) {
                    throw new DomainException('Это последний активный системный администратор. Сначала назначьте другого.');
                }
            }

            $this->run(
                'UPDATE users SET email = ?, last_name = ?, first_name = ?, middle_name = ?, birth_date = ?,
                        role_id = ?, is_active = ? WHERE id = ?',
                [$data['email'], $data['last_name'], $data['first_name'], $middle, $birth,
                 (int)$role['id'], (int)$data['is_active'], (int)$id]
            );
            if ($role['name'] === 'patient') {
                $this->ensurePatientCard($id);      // без карточки пациент не сможет записаться
            }
            $this->db->commit();
            return $current;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // Если у пользователя-пациента нет карточки в картотеке, создаём её
    private function ensurePatientCard($userId)
    {
        $has = $this->fetchOne('SELECT id FROM patients WHERE user_id = ?', [(int)$userId]);
        if (!$has) {
            $this->run(
                'INSERT INTO patients (user_id, last_name, first_name, middle_name, birth_date)
                 SELECT id, last_name, first_name, middle_name, birth_date FROM users WHERE id = ?',
                [(int)$userId]
            );
        }
    }
}

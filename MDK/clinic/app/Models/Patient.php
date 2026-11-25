<?php
class Patient extends Model
{
    // Список пациентов (с поиском по ФИО и телефону)
    public function all($query = '')
    {
        $params = [];
        $sql = 'SELECT p.*, u.email FROM patients p LEFT JOIN users u ON u.id = p.user_id';
        if ($query !== '') {
            // Символы % и _ имеют особый смысл в LIKE, поэтому экранируем их
            $safe = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
            $like = '%' . $safe . '%';
            $sql .= ' WHERE p.last_name LIKE ? OR p.first_name LIKE ? OR p.middle_name LIKE ? OR p.phone LIKE ?';
            $params = [$like, $like, $like, $like];
        }
        $sql .= ' ORDER BY p.last_name, p.first_name LIMIT 300';
        return $this->fetchAll($sql, $params);
    }

    public function find($id)
    {
        return $this->fetchOne(
            'SELECT p.*, u.email FROM patients p LEFT JOIN users u ON u.id = p.user_id WHERE p.id = ?',
            [(int)$id]
        );
    }

    // Карточка пациента по id аккаунта
    public function findByUserId($userId)
    {
        return $this->fetchOne('SELECT * FROM patients WHERE user_id = ?', [(int)$userId]);
    }

    // Создать (если $id пустой) или обновить карточку
    public function save($data, $id = null)
    {
        $params = [
            $data['last_name'], $data['first_name'],
            $data['middle_name'] !== '' ? $data['middle_name'] : null,
            $data['birth_date'] !== '' ? $data['birth_date'] : null,
            $data['phone'] !== '' ? $data['phone'] : null,
            $data['address'] !== '' ? $data['address'] : null,
        ];

        if ($id) {
            $params[] = (int)$id;
            $this->run(
                'UPDATE patients SET last_name = ?, first_name = ?, middle_name = ?,
                        birth_date = ?, phone = ?, address = ? WHERE id = ?',
                $params
            );
            return (int)$id;
        }

        $this->run(
            'INSERT INTO patients (last_name, first_name, middle_name, birth_date, phone, address)
             VALUES (?, ?, ?, ?, ?, ?)',
            $params
        );
        return (int)$this->db->lastInsertId();
    }

    // Удаление карты пациента (после отказа от обработки персональных данных).
    // 1. Незавершённые записи удаляются, а их время освобождается.
    // 2. Если у пациента есть завершённые приёмы, карточку НЕ удаляем, а обезличиваем:
    //    иначе пропадёт история выручки в отчётах.
    // 3. Аккаунт пациента для входа на сайт удаляется.
    // Возвращает 'deleted', 'anonymized' или null (карточка не найдена).
    public function deleteCard($patientId)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM patients WHERE id = ? FOR UPDATE');
            $stmt->execute([(int)$patientId]);
            $patient = $stmt->fetch();
            if (!$patient) {
                $this->db->rollBack();
                return null;
            }

            // 1. Удаляем незавершённые записи, а их время освобождаем
            $days = $this->fetchAll(
                "SELECT DISTINCT a.doctor_id, s.slot_date
                 FROM appointments a JOIN slots s ON s.id = a.slot_id
                 WHERE a.patient_id = ? AND a.status IN ('pending', 'confirmed')",
                [(int)$patientId]
            );
            $this->run("DELETE FROM appointments WHERE patient_id = ? AND status <> 'completed'", [(int)$patientId]);
            $slotModel = new Slot();
            foreach ($days as $day) {
                $slotModel->syncDay($day['doctor_id'], $day['slot_date']);
            }

            // 2. Есть ли завершённые приёмы?
            $row = $this->fetchOne('SELECT COUNT(*) AS n FROM appointments WHERE patient_id = ?', [(int)$patientId]);
            $userId = $patient['user_id'];

            if ((int)$row['n'] > 0) {
                $this->run(
                    "UPDATE patients SET last_name = 'Удалённый', first_name = 'пациент', middle_name = NULL,
                            birth_date = NULL, phone = NULL, address = NULL, user_id = NULL, pd_consent_refused = 1
                     WHERE id = ?",
                    [(int)$patientId]
                );
                $result = 'anonymized';
            } else {
                $this->run('DELETE FROM patients WHERE id = ?', [(int)$patientId]);
                $result = 'deleted';
            }

            // 3. Удаляем аккаунт пациента (только если это именно аккаунт с ролью patient)
            if ($userId) {
                $this->run(
                    "DELETE FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = 'patient')",
                    [(int)$userId]
                );
            }

            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

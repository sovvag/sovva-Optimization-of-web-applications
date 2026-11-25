<?php
class Permission extends Model
{
    // Список кодов прав для роли: ['doctors.view', 'appointment.create', ...]
    public function codesForRole($roleId)
    {
        $rows = $this->fetchAll(
            'SELECT p.code
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ?',
            [$roleId]
        );
        $codes = [];
        foreach ($rows as $row) {
            $codes[] = $row['code'];
        }
        return $codes;
    }

    public function all()
    {
        return $this->fetchAll('SELECT id, code, description FROM permissions ORDER BY id');
    }

    // Какие права у каких ролей сейчас: ['2-13' => true, ...] (роль-право)
    public function granted()
    {
        $set = [];
        foreach ($this->fetchAll('SELECT role_id, permission_id FROM role_permissions') as $row) {
            $set[$row['role_id'] . '-' . $row['permission_id']] = true;
        }
        return $set;
    }

    // Полностью заменить права ролей. $map: [id роли => [id права, ...]]
    // Всё в одной транзакции: либо сохранится всё, либо ничего.
    public function replaceAll($map)
    {
        $this->db->beginTransaction();
        try {
            $this->run('DELETE FROM role_permissions');
            foreach ($map as $roleId => $permIds) {
                foreach ($permIds as $permId) {
                    $this->run('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                        [(int)$roleId, (int)$permId]);
                }
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

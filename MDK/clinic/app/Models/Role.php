<?php
class Role extends Model
{
    // Найти id роли по её коду: idByName('patient') -> 1
    public function idByName($name)
    {
        $row = $this->fetchOne('SELECT id FROM roles WHERE name = ?', [$name]);
        return $row ? (int)$row['id'] : null;
    }

    public function all()
    {
        return $this->fetchAll('SELECT id, name, title FROM roles ORDER BY id');
    }

    public function find($id)
    {
        return $this->fetchOne('SELECT id, name, title FROM roles WHERE id = ?', [(int)$id]);
    }
}

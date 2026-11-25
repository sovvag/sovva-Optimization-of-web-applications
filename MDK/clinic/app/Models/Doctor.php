<?php
class Doctor extends Model
{
    // Все работающие врачи (для списка)
    public function allActive()
    {
        return $this->fetchAll(
            'SELECT d.*, s.name AS specialty
             FROM doctors d
             JOIN specialties s ON s.id = d.specialty_id
             WHERE d.is_active = 1
             ORDER BY d.last_name, d.first_name'
        );
    }

    // Один врач по адресной строке (slug), например smirnova-elena
    public function findBySlug($slug)
    {
        return $this->fetchOne(
            'SELECT d.*, s.name AS specialty
             FROM doctors d
             JOIN specialties s ON s.id = d.specialty_id
             WHERE d.slug = ? AND d.is_active = 1',
            [$slug]
        );
    }

 public function findById($id) {
  return $this->fetchOne("SELECT d.*,s.name AS specialty FROM doctors d JOIN specialties s ON s.id=d.specialty_id WHERE d.id=? AND d.is_active=1",[(int)$id]);
 }

    // ---------- Для руководителя ----------

    // Все врачи, включая неработающих
    public function allForManage()
    {
        return $this->fetchAll(
            'SELECT d.*, s.name AS specialty
             FROM doctors d JOIN specialties s ON s.id = d.specialty_id
             ORDER BY d.is_active DESC, d.last_name, d.first_name'
        );
    }

    // Врач по id независимо от того, работает он или нет
    public function findAny($id)
    {
        return $this->fetchOne(
            'SELECT d.*, s.name AS specialty
             FROM doctors d JOIN specialties s ON s.id = d.specialty_id
             WHERE d.id = ?',
            [(int)$id]
        );
    }

    public function specialties()
    {
        return $this->fetchAll('SELECT id, name FROM specialties ORDER BY name');
    }

    public function specialtyExists($id)
    {
        return $this->fetchOne('SELECT id FROM specialties WHERE id = ?', [(int)$id]) !== null;
    }

    // Уникальный адрес страницы: smirnova-elena, а если занят, то smirnova-elena-2
    public function makeSlug($lastName, $firstName)
    {
        $base = translit($lastName . ' ' . $firstName);
        if ($base === '') {
            $base = 'doctor';
        }
        $slug = $base;
        $n = 2;
        while ($this->fetchOne('SELECT id FROM doctors WHERE slug = ?', [$slug]) !== null) {
            $slug = $base . '-' . $n;
            $n++;
        }
        return $slug;
    }

    // Добавить (если $id пустой) или изменить врача. Адрес страницы при изменении не меняем.
    public function save($data, $id = null)
    {
        $values = [
            (int)$data['specialty_id'], $data['last_name'], $data['first_name'],
            $data['middle_name'] !== '' ? $data['middle_name'] : null,
            (int)$data['experience_years'], $data['patient_category'],
            $data['skills'] !== '' ? $data['skills'] : null, $data['price'], (int)$data['is_active'],
        ];

        if ($id) {
            $values[] = (int)$id;
            $this->run(
                'UPDATE doctors SET specialty_id = ?, last_name = ?, first_name = ?, middle_name = ?,
                        experience_years = ?, patient_category = ?, skills = ?, price = ?, is_active = ?
                 WHERE id = ?',
                $values
            );
            return (int)$id;
        }

        $values[] = $this->makeSlug($data['last_name'], $data['first_name']);
        $this->run(
            'INSERT INTO doctors (specialty_id, last_name, first_name, middle_name, experience_years,
                                  patient_category, skills, price, is_active, slug)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            $values
        );
        return (int)$this->db->lastInsertId();
    }

    public function setPhoto($id, $path)
    {
        $this->run('UPDATE doctors SET photo_path = ? WHERE id = ?', [$path, (int)$id]);
    }

    // Есть ли у врача будущие активные записи (тогда его нельзя отключить)
    public function futureAppointments($id)
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS n
             FROM appointments a JOIN slots s ON s.id = a.slot_id
             WHERE a.doctor_id = ? AND a.status IN ('pending', 'confirmed') AND s.slot_date >= ?",
            [(int)$id, date('Y-m-d')]
        );
        return (int)$row['n'];
    }
}

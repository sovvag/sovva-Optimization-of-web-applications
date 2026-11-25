<?php
// ============================================================
// Model.php: базовый класс для всех моделей (работа с БД).
// Все SQL-запросы выполняем ТОЛЬКО через подготовленные запросы:
// значения передаём отдельным массивом, а не вставляем в строку.
// Это защита от SQL-инъекций.
// ============================================================
class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    // Получить все строки: fetchAll('SELECT * FROM doctors WHERE id = ?', [5])
    protected function fetchAll($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Получить одну строку (или null, если ничего не найдено)
    protected function fetchOne($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    // Выполнить INSERT, UPDATE или DELETE
    protected function run($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

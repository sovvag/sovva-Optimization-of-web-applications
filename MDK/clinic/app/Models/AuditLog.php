<?php
// Журнал действий. Из него строится отчёт по работе администраторов.
class AuditLog extends Model
{
    public function add($userId, $action, $entity, $entityId = null, $details = null)
    {
        $this->run(
            'INSERT INTO audit_log (user_id, action, entity, entity_id, details, ip)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $userId, $action, $entity, $entityId, $details,
                isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : null,
            ]
        );
    }

    // Последние действия (для страницы "Журнал")
    public function latest($limit = 200)
    {
        $limit = max(1, min(500, (int)$limit));
        return $this->fetchAll(
            'SELECT l.id, l.action, l.entity, l.entity_id, l.details, l.ip, l.created_at,
                    u.email, u.last_name, u.first_name
             FROM audit_log l LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.id DESC LIMIT ' . $limit
        );
    }
}

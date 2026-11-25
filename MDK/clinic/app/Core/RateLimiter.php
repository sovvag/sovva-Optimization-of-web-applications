<?php
// ============================================================
// RateLimiter.php: защита от подбора пароля.
// Считаем неудачные попытки входа с одного IP-адреса.
// ============================================================
class RateLimiter extends Model
{
    // Заблокирован ли этот IP
    public function isBlocked($ip)
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM login_attempts
             WHERE ip = ? AND attempted_at > (NOW() - INTERVAL ' . (int)LOGIN_BLOCK_MINUTES . ' MINUTE)',
            [$ip]
        );
        return (int)$row['cnt'] >= LOGIN_MAX_ATTEMPTS;
    }

    // Записать неудачную попытку
    public function addFailure($ip, $email)
    {
        $this->run('INSERT INTO login_attempts (ip, email) VALUES (?, ?)', [$ip, mb_substr($email, 0, 150)]);
        // Заодно чистим записи старше суток
        $this->run('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
    }

    // После успешного входа сбрасываем счётчик
    public function clear($ip)
    {
        $this->run('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
    }
}

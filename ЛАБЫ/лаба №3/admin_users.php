<?php
session_start();
include 'includes/db.php';

// Проверка авторизации через обычную сессию
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

// Проверка роли админа
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    header('Location: account.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи - Админ-панель</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #F8F9FC;
            color: #1E2A3E;
        }
        .admin-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-header-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            font-family: 'Manrope', sans-serif;
            font-size: 24px;
            background: linear-gradient(135deg, #5F9EF0, #3A7BD5);
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .admin-nav {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .admin-nav a {
            text-decoration: none;
            color: #5B6E8C;
            font-weight: 500;
            transition: all 0.2s;
            padding: 8px 16px;
            border-radius: 8px;
        }
        .admin-nav a:hover {
            color: #5F9EF0;
            background: #F0F4FF;
        }
        .admin-nav a.active {
            color: #5F9EF0;
            background: #F0F4FF;
        }
        .logout-btn {
            background: #FEE2E2;
            color: #DC2626 !important;
        }
        .logout-btn:hover {
            background: #FECACA !important;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .page-header {
            margin: 30px 0 20px;
        }
        .page-header h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 28px;
        }
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid #E1E8F0;
        }
        th {
            background: #F8F9FC;
            font-weight: 600;
            font-size: 13px;
            color: #5B6E8C;
        }
        tr:hover {
            background: #F8F9FC;
        }
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .role-user { background: #DBEAFE; color: #1D4ED8; }
        .role-admin { background: #DCFCE7; color: #065F46; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #5F9EF0;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .admin-header-inner {
                flex-direction: column;
                gap: 15px;
            }
            .admin-nav {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-header-inner">
            <h1>Управление магазином</h1>
            <nav class="admin-nav">
                <a href="admin_dashboard.php">Главная</a>
                <a href="admin_orders.php">Заказы</a>
                <a href="admin_products.php">Товары</a>
                <a href="admin_users.php" class="active">Пользователи</a>
                <a href="account.php?logout=1" class="logout-btn">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <a href="admin_dashboard.php" class="back-link">← Назад в админ-панель</a>
        
        <div class="page-header" data-aos="fade-up">
            <h2>Пользователи</h2>
        </div>

        <div class="table-container" data-aos="fade-up" data-aos-delay="100">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Роль</th>
                        <th>Дата регистрации</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = $pdo->query("
                        SELECT * FROM users 
                        ORDER BY id
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($users) > 0):
                        foreach($users as $user):
                            $roleClass = $user['role'] == 'admin' ? 'role-admin' : 'role-user';
                    ?>
                    <tr>
                        <td>#<?= $user['id'] ?></td>
                        <td><strong><?= htmlspecialchars($user['full_name'] ?? 'Не указано') ?></strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></td>
                        <td><span class="role-badge <?= $roleClass ?>"><?= $user['role'] ?? 'user' ?></span></td>
                        <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #5B6E8C; padding: 30px;">Пользователей пока нет</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
    </script>
</body>
</html>
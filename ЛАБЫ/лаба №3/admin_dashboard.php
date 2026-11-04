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

$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'];
$stats['orders'] = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch(PDO::FETCH_ASSOC)['count'];
$stats['products'] = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch(PDO::FETCH_ASSOC)['count'];
$stats['low_stock'] = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock < 5")->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0 40px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #5F9EF0;
        }
        .stat-card .label {
            color: #5B6E8C;
            font-size: 14px;
            margin-top: 4px;
        }
        .stat-card.danger .number { color: #DC2626; }
        .stat-card.success .number { color: #22C55E; }
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #E1E8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h2 {
            font-family: 'Manrope', sans-serif;
            font-size: 18px;
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
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-new { background: #DBEAFE; color: #1D4ED8; }
        .status-processing { background: #FEF3C7; color: #92400E; }
        .status-shipped { background: #D1FAE5; color: #065F46; }
        .status-delivered { background: #D1FAE5; color: #065F46; }
        .status-cancelled { background: #FEE2E2; color: #DC2626; }
        .welcome-text {
            color: #5B6E8C;
        }
        .welcome-text strong {
            color: #1E2A3E;
        }
        .back-to-site {
            display: inline-block;
            margin-top: 20px;
            color: #5F9EF0;
            text-decoration: none;
        }
        .back-to-site:hover {
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
                <a href="admin_dashboard.php" class="active">Главная</a>
                <a href="admin_orders.php">Заказы</a>
                <a href="admin_products.php">Товары</a>
                <a href="admin_users.php">Пользователи</a>
                <a href="account.php?logout=1" class="logout-btn">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="margin: 30px 0 20px;" data-aos="fade-up">
            <p class="welcome-text">Здравствуйте, <strong><?= $_SESSION['user_name'] ?? 'Администратор' ?></strong>!</p>
            <a href="index.php" class="back-to-site">← Вернуться на сайт</a>
        </div>

        <div class="stats-grid" data-aos="fade-up">
            <div class="stat-card success">
                <div class="number"><?= $stats['users'] ?></div>
                <div class="label">Пользователей</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['orders'] ?></div>
                <div class="label">Заказов</div>
            </div>
            <div class="stat-card success">
                <div class="number"><?= $stats['products'] ?></div>
                <div class="label">Товаров</div>
            </div>
            <div class="stat-card <?= $stats['low_stock'] > 0 ? 'danger' : '' ?>">
                <div class="number"><?= $stats['low_stock'] ?></div>
                <div class="label">Товаров с низким запасом</div>
            </div>
        </div>

        <div class="table-container" data-aos="fade-up" data-aos-delay="100">
            <div class="table-header">
                <h2>Последние заказы</h2>
                <a href="admin_orders.php" style="color: #5F9EF0; text-decoration: none;">Все заказы →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Пользователь</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = $pdo->query("
                        SELECT o.*, u.full_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($orders) > 0):
                        foreach($orders as $order):
                            $statusClass = 'status-' . $order['status'];
                    ?>
                    <tr>
                        <td><strong><?= $order['order_number'] ?></strong></td>
                        <td><?= $order['full_name'] ?? 'Гость' ?></td>
                        <td><?= number_format($order['total'], 0, ',', ' ') ?> ₽</td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= $order['status'] ?></span></td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align: center; color: #5B6E8C; padding: 30px;">Заказов пока нет</td></tr>
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
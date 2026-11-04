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

// Обновление статуса заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    
    header('Location: admin_orders.php?updated=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказы - Админ-панель</title>
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
            min-width: 700px;
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
        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .status-form select {
            padding: 4px 8px;
            border: 1px solid #E1E8F0;
            border-radius: 8px;
            font-size: 13px;
        }
        .status-form button {
            padding: 4px 12px;
            background: #5F9EF0;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }
        .status-form button:hover {
            background: #3A7BD5;
        }
        .alert {
            padding: 12px 20px;
            background: #D1FAE5;
            color: #065F46;
            border-radius: 12px;
            margin-bottom: 20px;
        }
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
                <a href="admin_orders.php" class="active">Заказы</a>
                <a href="admin_products.php">Товары</a>
                <a href="admin_users.php">Пользователи</a>
                <a href="account.php?logout=1" class="logout-btn">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <a href="admin_dashboard.php" class="back-link">← Назад в админ-панель</a>
        
        <div class="page-header" data-aos="fade-up">
            <h2>Управление заказами</h2>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert" data-aos="fade-up">Статус заказа обновлён</div>
        <?php endif; ?>

        <div class="table-container" data-aos="fade-up" data-aos-delay="100">
            <table>
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Пользователь</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = $pdo->query("
                        SELECT o.*, u.full_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC
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
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <input type="hidden" name="action" value="update_status">
                                <select name="status">
                                    <option value="new" <?= $order['status'] == 'new' ? 'selected' : '' ?>>Новый</option>
                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>В обработке</option>
                                    <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Отправлен</option>
                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Отменён</option>
                                </select>
                                <button type="submit">Обновить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align: center; color: #5B6E8C; padding: 30px;">Заказов пока нет</td></tr>
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
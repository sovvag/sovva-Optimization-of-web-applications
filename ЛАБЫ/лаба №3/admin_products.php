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

// Обновление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $product_id = $_POST['product_id'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->execute([$stock, $product_id]);
    
    header('Location: admin_products.php?updated=1');
    exit;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товары - Админ-панель</title>
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
        .stock-form {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .stock-form input[type="number"] {
            width: 80px;
            padding: 4px 8px;
            border: 1px solid #E1E8F0;
            border-radius: 8px;
            font-size: 13px;
        }
        .stock-form button {
            padding: 4px 12px;
            background: #5F9EF0;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }
        .stock-form button:hover {
            background: #3A7BD5;
        }
        .stock-low {
            color: #DC2626;
            font-weight: 600;
        }
        .stock-normal {
            color: #22C55E;
        }
        .badge {
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-new { background: #DBEAFE; color: #1D4ED8; }
        .badge-popular { background: #FEF3C7; color: #92400E; }
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
                <a href="admin_orders.php">Заказы</a>
                <a href="admin_products.php" class="active">Товары</a>
                <a href="admin_users.php">Пользователи</a>
                <a href="account.php?logout=1" class="logout-btn">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <a href="admin_dashboard.php" class="back-link">← Назад в админ-панель</a>
        
        <div class="page-header" data-aos="fade-up">
            <h2>Управление товарами</h2>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert" data-aos="fade-up">Количество товара обновлено</div>
        <?php endif; ?>

        <div class="table-container" data-aos="fade-up" data-aos-delay="100">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Остаток</th>
                        <th>Метки</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $products = $pdo->query("
                        SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.id
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($products) > 0):
                        foreach($products as $product):
                            $stockClass = $product['stock'] < 5 ? 'stock-low' : 'stock-normal';
                    ?>
                    <tr>
                        <td>#<?= $product['id'] ?></td>
                        <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                        <td><?= $product['category_name'] ?? 'Без категории' ?></td>
                        <td><?= number_format($product['price'], 0, ',', ' ') ?> ₽</td>
                        <td class="<?= $stockClass ?>"><?= $product['stock'] ?></td>
                        <td>
                            <?php if ($product['is_new']): ?>
                                <span class="badge badge-new">Новинка</span>
                            <?php endif; ?>
                            <?php if ($product['is_popular']): ?>
                                <span class="badge badge-popular">Популярное</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" class="stock-form">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="action" value="update_stock">
                                <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0">
                                <button type="submit">Обновить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" style="text-align: center; color: #5B6E8C; padding: 30px;">Товаров пока нет</td></tr>
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
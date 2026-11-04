<?php
// Получаем количество товаров в корзине и избранном
$session_id = session_id();
$cart_count = 0;
$wishlist_count = 0;

if (isset($pdo)) {
    try {
        // Количество в корзине
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ? OR user_id = ?");
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $stmt->execute([$session_id, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cart_count = $result['total'] ?? 0;
        
        // Количество в избранном
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT product_id) as total FROM wishlist WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $wishlist_count = $result['total'] ?? 0;
    } catch(PDOException $e) {
        $cart_count = 0;
        $wishlist_count = 0;
    }
}

// Определяем корневой путь 
$root_path = '';
if (strpos($_SERVER['REQUEST_URI'], '/includes/') !== false) {
    $root_path = '../';
} elseif (strpos($_SERVER['REQUEST_URI'], '/admin_') !== false) {
    $root_path = '../';
} else {
    $root_path = '';
}

// Проверяем авторизацию
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Войти';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lens & Look - Интернет-магазин оптики</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $root_path ?>assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #5F9EF0;
            --primary-dark: #3A7BD5;
            --secondary: #F0F4FF;
            --text: #1E2A3E;
            --text-light: #5B6E8C;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-bg: rgba(255,255,255,0.95);
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text);
            line-height: 1.5;
        }
        
        h1, h2, h3, h4, .logo, .nav-link, .btn {
            font-family: 'Manrope', sans-serif;
            font-weight: 600;
        }
        
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .logo {
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .search-bar {
            flex: 1;
            max-width: 380px;
            min-width: 150px;
        }
        
        .search-form {
            display: flex;
            gap: 8px;
            width: 100%;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 18px;
            border: 2px solid #E1E8F0;
            border-radius: 40px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(95,158,240,0.1);
        }
        
        .search-btn {
            background: #5F9EF0;
            border: none;
            border-radius: 40px;
            padding: 0 18px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.02);
        }
        
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
        
        /* Навигационные ссылки */
        .nav-links {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-link {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            transition: all 0.2s;
            font-size: 14px;
            padding: 6px 14px;
            border-radius: 40px;
        }
        
        .nav-link:hover {
            color: var(--primary);
            background: #F0F4FF;
        }
        
        .nav-link.active {
            color: var(--primary);
            background: #F0F4FF;
        }
        
        /* Иконки (профиль, избранное, корзина) */
        .icon-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.3s;
            background: transparent;
        }
        
        .icon-btn:hover {
            background: #F0F4FF;
            color: var(--primary);
        }
        
        .icon-btn .count-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .icon-btn .badge-dot {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: #22C55E;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .icon-btn .nav-link-text {
            display: none;
        }
        
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(95,158,240,0.3);
        }
        
        /* Адаптив для мобильных */
        @media (max-width: 992px) {
            .search-bar {
                max-width: 280px;
            }
        }
        
        @media (max-width: 768px) {
            .header-inner {
                flex-direction: column;
                padding: 12px 0;
                gap: 12px;
            }
            .search-bar {
                max-width: 100%;
                width: 100%;
            }
            .nav-links {
                width: 100%;
                justify-content: center;
                gap: 4px;
            }
            .nav-link {
                font-size: 13px;
                padding: 4px 10px;
            }
            .icon-btn .nav-link-text {
                display: inline;
                font-size: 13px;
                margin-left: 4px;
            }
            .icon-btn {
                width: auto;
                padding: 0 10px;
                border-radius: 40px;
            }
            .icon-btn:hover {
                background: #F0F4FF;
            }
            .logo {
                font-size: 22px;
            }
        }
        
        @media (max-width: 480px) {
            .nav-link {
                font-size: 11px;
                padding: 4px 8px;
            }
            .icon-btn .nav-link-text {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="container header-inner">
        <a href="<?= $root_path ?>index.php" class="logo">Lens & Look</a>
        
        <div class="search-bar">
            <form action="<?= $root_path ?>search.php" method="GET" class="search-form" role="search">
                <label for="searchInput" class="visually-hidden">Поиск товаров</label>
                <input type="text" name="q" id="searchInput" class="search-input" placeholder="Поиск линз, очков..." required autocomplete="off">
                <button type="submit" class="search-btn" aria-label="Найти">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                </button>
            </form>
        </div>
        
        <div class="nav-links">
            <!-- Навигационные ссылки -->
            <a href="<?= $root_path ?>catalog.php" class="nav-link">Каталог</a>
            <a href="<?= $root_path ?>delivery.php" class="nav-link">Доставка</a>
            <a href="<?= $root_path ?>payment.php" class="nav-link">Оплата</a>
            <a href="<?= $root_path ?>returns.php" class="nav-link">Возврат</a>
            
            <!-- Иконка пользователя (Личный кабинет) -->
            <a href="<?= $root_path ?>account.php" class="icon-btn" title="Личный кабинет">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <?php if ($is_logged_in): ?>
                    <span class="badge-dot"></span>
                <?php endif; ?>
                <span class="nav-link-text">Профиль</span>
            </a>
            
            <!-- Избранное -->
            <a href="<?= $root_path ?>wishlist.php" class="icon-btn" title="Избранное">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <?php if ($wishlist_count > 0): ?>
                    <span class="count-badge"><?= $wishlist_count ?></span>
                <?php endif; ?>
                <span class="nav-link-text">Избранное</span>
            </a>
            
            <!-- Корзина -->
            <a href="<?= $root_path ?>cart.php" class="icon-btn" title="Корзина">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <?php if ($cart_count > 0): ?>
                    <span class="count-badge"><?= $cart_count ?></span>
                <?php endif; ?>
                <span class="nav-link-text">Корзина</span>
            </a>
        </div>
    </div>
</header>
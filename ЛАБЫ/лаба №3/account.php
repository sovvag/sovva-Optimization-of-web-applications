<?php
include 'includes/db.php';
include 'includes/functions.php';

session_start();

// Проверяем авторизацию
$is_logged_in = isset($_SESSION['user_id']);
$user_data = null;
$is_admin = false;

if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_data && isset($user_data['role']) && $user_data['role'] === 'admin') {
        $is_admin = true;
    }
}

// Обработка регистрации
$registration_error = '';
$registration_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Простая валидация
    if (empty($email) || empty($password) || empty($full_name)) {
        $registration_error = 'Заполните обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== 'admin') {
        $registration_error = 'Неверный формат email';
    } else {
        try {
            // Проверка на существование
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $registration_error = 'Пользователь с таким email уже существует';
            } else {
                // Сохраняем пароль в открытом виде (для простоты)
                $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone, is_guest) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$email, $password, $full_name, $phone]);
                
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                
                $registration_success = "Регистрация успешна! Добро пожаловать!";
                
                // Обновляем данные
                $is_logged_in = true;
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user_data && isset($user_data['role']) && $user_data['role'] === 'admin') {
                    $is_admin = true;
                }
                
                // Переносим корзину
                $session_id = session_id();
                $stmt = $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
                $stmt->execute([$user_id, $session_id]);
            }
        } catch(PDOException $e) {
            $registration_error = "Ошибка при регистрации: " . $e->getMessage();
        }
    }
}

// Обработка входа
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $login_error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Проверяем пароль (простое сравнение)
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            $is_logged_in = true;
            $user_data = $user;
            
            if ($user_data && isset($user_data['role']) && $user_data['role'] === 'admin') {
                $is_admin = true;
            }
            
            // Переносим корзину
            $session_id = session_id();
            $stmt = $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
            $stmt->execute([$user['id'], $session_id]);
            
            // Обновляем страницу
            header('Location: account.php');
            exit;
        } else {
            $login_error = 'Неверный email или пароль';
        }
    }
}

// Выход
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: account.php');
    exit;
}

include 'includes/header.php';
?>

<main class="container">
    <h1 style="margin: 40px 0;">Личный кабинет</h1>
    
    <?php if ($is_logged_in && $user_data): ?>
        <!-- Личный кабинет для авторизованных пользователей -->
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 40px;">
            <!-- Боковое меню -->
            <div style="background: #F8F9FC; padding: 30px; border-radius: 24px; height: fit-content; position: sticky; top: 100px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 80px; height: 80px; background: #5F9EF0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 32px; color: white;">
                        <?= mb_substr($user_data['full_name'] ?? 'П', 0, 1) ?>
                    </div>
                    <h3><?= htmlspecialchars($user_data['full_name'] ?? 'Пользователь') ?></h3>
                    <p style="color: #5B6E8C;"><?= htmlspecialchars($user_data['email'] ?? '') ?></p>
                    <?php if ($is_admin): ?>
                        <span style="display: inline-block; margin-top: 8px; background: #DCFCE7; color: #065F46; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500;">Администратор</span>
                    <?php endif; ?>
                </div>
                <hr style="margin: 20px 0;">
                <nav style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="#" onclick="showTab('profile')" style="text-decoration: none; color: #1E2A3E; padding: 10px 14px; border-radius: 12px; transition: all 0.2s;" class="tab-link active">
                        <span style="margin-right: 10px;">👤</span> Профиль
                    </a>
                    <a href="#" onclick="showTab('orders')" style="text-decoration: none; color: #1E2A3E; padding: 10px 14px; border-radius: 12px; transition: all 0.2s;" class="tab-link">
                        <span style="margin-right: 10px;">📦</span> Мои заказы
                    </a>
                    <a href="#" onclick="showTab('favorites')" style="text-decoration: none; color: #1E2A3E; padding: 10px 14px; border-radius: 12px; transition: all 0.2s;" class="tab-link">
                        <span style="margin-right: 10px;">❤️</span> Избранное
                    </a>
                    
                    <?php if ($is_admin): ?>
                        <hr style="margin: 10px 0;">
                        <a href="admin_dashboard.php" style="text-decoration: none; color: #5F9EF0; padding: 10px 14px; border-radius: 12px; background: #F0F4FF; transition: all 0.2s;">
                            <span style="margin-right: 10px;">⚙️</span> Админ-панель
                        </a>
                    <?php endif; ?>
                    
                    <hr style="margin: 10px 0;">
                    <a href="?logout=1" style="text-decoration: none; color: #dc3545; padding: 10px 14px; border-radius: 12px; transition: all 0.2s;">
                        <span style="margin-right: 10px;">🚪</span> Выйти
                    </a>
                </nav>
            </div>
            
            <!-- Контент -->
            <div>
                <div id="profile-tab" class="tab-content">
                    <div style="background: white; padding: 30px; border-radius: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h2 style="margin-bottom: 20px;">Профиль</h2>
                        <div style="display: grid; gap: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #5B6E8C;">ФИО</label>
                                <input type="text" value="<?= htmlspecialchars($user_data['full_name'] ?? '') ?>" 
                                       style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px;" disabled>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #5B6E8C;">Email</label>
                                <input type="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" 
                                       style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px;" disabled>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #5B6E8C;">Телефон</label>
                                <input type="tel" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" 
                                       style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px;" disabled>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #5B6E8C;">Роль</label>
                                <input type="text" value="<?= $is_admin ? 'Администратор' : 'Пользователь' ?>" 
                                       style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px;" disabled>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #5B6E8C;">Дата регистрации</label>
                                <input type="text" value="<?= date('d.m.Y H:i', strtotime($user_data['created_at'] ?? 'now')) ?>" 
                                       style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px;" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="orders-tab" class="tab-content" style="display: none;">
                    <div style="background: white; padding: 30px; border-radius: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h2 style="margin-bottom: 20px;">История заказов</h2>
                        <?php
                        // Получаем заказы пользователя
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$user_data['id']]);
                        $user_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if (count($user_orders) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #F8F9FC;">
                                            <th style="padding: 12px; text-align: left;">№ заказа</th>
                                            <th style="padding: 12px; text-align: left;">Сумма</th>
                                            <th style="padding: 12px; text-align: left;">Статус</th>
                                            <th style="padding: 12px; text-align: left;">Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($user_orders as $order): ?>
                                        <tr>
                                            <td style="padding: 12px; border-bottom: 1px solid #E1E8F0;"><strong><?= $order['order_number'] ?></strong></td>
                                            <td style="padding: 12px; border-bottom: 1px solid #E1E8F0;"><?= number_format($order['total'], 0, ',', ' ') ?> ₽</td>
                                            <td style="padding: 12px; border-bottom: 1px solid #E1E8F0;">
                                                <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; background: <?= $order['status'] == 'new' ? '#DBEAFE' : ($order['status'] == 'processing' ? '#FEF3C7' : ($order['status'] == 'delivered' ? '#D1FAE5' : '#FEE2E2')) ?>; color: <?= $order['status'] == 'new' ? '#1D4ED8' : ($order['status'] == 'processing' ? '#92400E' : ($order['status'] == 'delivered' ? '#065F46' : '#DC2626')) ?>;">
                                                    <?= $order['status'] == 'new' ? 'Новый' : ($order['status'] == 'processing' ? 'В обработке' : ($order['status'] == 'delivered' ? 'Доставлен' : 'Отменён')) ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px; border-bottom: 1px solid #E1E8F0;"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: #5B6E8C; text-align: center; padding: 40px;">У вас пока нет заказов</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="favorites-tab" class="tab-content" style="display: none;">
                    <div style="background: white; padding: 30px; border-radius: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h2 style="margin-bottom: 20px;">Избранное</h2>
                        <?php
                        include 'includes/wishlist_functions.php';
                        $wishlist_items = getWishlistItems($pdo, session_id());
                        ?>
                        <?php if (count($wishlist_items) > 0): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                                <?php foreach($wishlist_items as $item): ?>
                                <div style="background: #F8F9FC; border-radius: 16px; padding: 15px; text-align: center; transition: all 0.3s;">
                                    <img src="/assets/images/<?= $item['image'] ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 12px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22150%22%3E%3Crect width=%22200%22 height=%22150%22 fill=%225F9EF0%22/%3E%3C/svg%3E'">
                                    <h4 style="margin: 10px 0 5px; font-size: 14px;"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p style="color: #5F9EF0; font-weight: 700;"><?= number_format($item['price'], 0, ',', ' ') ?> ₽</p>
                                    <button class="btn" style="padding: 8px 16px; font-size: 12px; margin-top: 8px;" onclick="window.location.href='catalog.php'">В каталог</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #5B6E8C; text-align: center; padding: 40px;">Нет избранных товаров</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function showTab(tabName) {
            // Скрыть все вкладки
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            // Показать выбранную
            document.getElementById(`${tabName}-tab`).style.display = 'block';
            
            // Обновить активную ссылку
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
                link.style.background = 'transparent';
                link.style.color = '#1E2A3E';
            });
            
            // Найти активную ссылку
            document.querySelectorAll('.tab-link').forEach(link => {
                if (link.textContent.includes(tabName === 'profile' ? 'Профиль' : 
                                              tabName === 'orders' ? 'Мои заказы' : 'Избранное')) {
                    link.classList.add('active');
                    link.style.background = '#5F9EF0';
                    link.style.color = 'white';
                }
            });
        }
        </script>
        
    <?php else: ?>
        <!-- Формы регистрации и входа -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <!-- Форма входа -->
            <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h2 style="margin-bottom: 20px;">Вход</h2>
                <?php if ($login_error): ?>
                    <div style="background: #FEE2E2; color: #DC2626; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $login_error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                        <input type="text" name="email" required placeholder="Введите email или admin" style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px; transition: all 0.2s;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Пароль</label>
                        <input type="password" name="password" required placeholder="••••••" style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px; transition: all 0.2s;">
                    </div>
                    <button type="submit" name="login" class="btn" style="width: 100%;">Войти</button>
                </form>
                <p style="text-align: center; margin-top: 20px; color: #5B6E8C; font-size: 14px;">
                    Нет аккаунта? <a href="#" onclick="document.querySelector('.register-form').scrollIntoView({behavior: 'smooth'}); return false;" style="color: #5F9EF0; text-decoration: none;">Зарегистрируйтесь</a>
                </p>
                <div style="margin-top: 15px; padding: 12px; background: #F0F4FF; border-radius: 12px;">
                    <p style="color: #5B6E8C; font-size: 13px; margin: 0;">
                        <strong>Для администратора:</strong><br>
                        Логин: <strong>admin</strong><br>
                        Пароль: <strong>123456</strong>
                    </p>
                </div>
            </div>
            
            <!-- Форма регистрации -->
            <div style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h2 style="margin-bottom: 20px;">Регистрация</h2>
                <?php if ($registration_error): ?>
                    <div style="background: #FEE2E2; color: #DC2626; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $registration_error ?></div>
                <?php endif; ?>
                <?php if ($registration_success): ?>
                    <div style="background: #D1FAE5; color: #065F46; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?= $registration_success ?></div>
                <?php endif; ?>
                <form method="POST" class="register-form">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">ФИО *</label>
                        <input type="text" name="full_name" required placeholder="Иван Иванов" style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px; transition: all 0.2s;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email *</label>
                        <input type="text" name="email" required placeholder="your@email.com" style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px; transition: all 0.2s;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Телефон</label>
                        <input type="tel" name="phone" placeholder="+7 (___) ___-__-__" style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px; transition: all 0.2s;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Пароль *</label>
                        <input type="password" name="password" required placeholder="минимум 6 символов" minlength="6" style="width: 100%; padding: 12px; border: 2px solid #E1E8F0; border-radius: 12px; font-size: 16px; transition: all 0.2s;">
                    </div>
                    <button type="submit" name="register" class="btn" style="width: 100%;">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
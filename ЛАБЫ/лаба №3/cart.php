<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/auth_functions.php';


// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['id'] ?? 0;
    $session_id = session_id();
    
    if ($action === 'add') {
        addToCart($pdo, $session_id, $product_id);
    } elseif ($action === 'remove') {
        removeFromCart($pdo, $session_id, $product_id);
    } elseif ($action === 'update') {
        $quantity = $_POST['quantity'] ?? 1;
        updateCartQuantity($pdo, $session_id, $product_id, $quantity);
    } elseif ($action === 'checkout') {
        // Проверяем авторизацию
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'need_auth' => true]);
            exit;
        }
        
        // Создаем заказ
        $cart_items = getCartItems($pdo, $session_id);
        $total = getCartTotal($pdo, $session_id);
        $delivery_address = $_POST['address'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'card';
        
        $result = createOrder($pdo, $_SESSION['user_id'], $cart_items, $total, $delivery_address, $payment_method);
        echo json_encode($result);
        exit;
    }
    
    // Возвращаем общее количество
    $count = getCartCount($pdo, $session_id);
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

// Отображение корзины (GET запрос)
include 'includes/header.php';

$session_id = session_id();
$cart_items = getCartItems($pdo, $session_id);
$cart_total = getCartTotal($pdo, $session_id);
$is_logged_in = isLoggedIn();
$current_user = $is_logged_in ? getCurrentUser($pdo) : null;
?>

<main class="container">
    <link rel="stylesheet" href="style.css">
    <h1 style="margin: 40px 0 30px;">Корзина</h1>
    
    <?php if (empty($cart_items)): ?>
        <!-- Пустая корзина -->
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <h2>Ваша корзина пуста</h2>
            <p>Добавьте товары из каталога, чтобы оформить заказ</p>
            <a href="/catalog.php" class="btn">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <!-- Корзина с товарами -->
        <div class="cart-layout">
            <div class="cart-items">
                <div class="cart-header">
                    <div>Товар</div>
                    <div>Название</div>
                    <div>Цена</div>
                    <div>Количество</div>
                    <div></div>
                </div>
                
                <?php foreach($cart_items as $item): ?>
                <div class="cart-item" data-id="<?= $item['product_id'] ?>">
                    <img src="/assets/images/<?= $item['image'] ?>" 
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect width=%2280%22 height=%2280%22 fill=%225F9EF0%22/%3E%3C/svg%3E'">
                    <div>
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>Арт: <?= $item['product_id'] ?></p>
                    </div>
                    <div class="price"><?= number_format($item['price'], 0, ',', ' ') ?> ₽</div>
                    <div class="quantity-control">
                        <button class="qty-btn btn-update" data-id="<?= $item['product_id'] ?>" data-change="-1">−</button>
                        <span id="qty-<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                        <button class="qty-btn btn-update" data-id="<?= $item['product_id'] ?>" data-change="1">+</button>
                    </div>
                    <button class="remove-btn btn-remove" data-id="<?= $item['product_id'] ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Итого</h3>
                <div class="summary-row">
                    <span>Товары (<?= count($cart_items) ?> шт.):</span>
                    <span><strong><?= number_format($cart_total, 0, ',', ' ') ?> ₽</strong></span>
                </div>
                <div class="summary-row">
                    <span>Доставка:</span>
                    <span>от 200 ₽</span>
                </div>
                <hr>
                <div class="summary-row total">
                    <span>К оплате:</span>
                    <span><?= number_format($cart_total, 0, ',', ' ') ?> ₽</span>
                </div>
                <button class="btn checkout-btn" id="checkoutBtn">Оформить заказ</button>
                <a href="/catalog.php" class="continue-link">← Продолжить покупки</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Модальное окно авторизации/регистрации -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Добро пожаловать</h2>
            <button class="modal-close" aria-label="Закрыть">&times;</button>
        </div>
        
        <!-- Переключатель между входом и регистрацией -->
        <div class="auth-tabs" role="tablist">
            <button class="tab-btn active" data-tab="login" role="tab" aria-selected="true">Вход</button>
            <button class="tab-btn" data-tab="register" role="tab" aria-selected="false">Регистрация</button>
        </div>
        
        <!-- Форма входа -->
        <form id="loginForm" class="auth-form active" aria-label="Форма входа">
            <div class="form-group">
                <label for="loginEmail">Email</label>
                <input type="email" id="loginEmail" name="email" placeholder="your@email.com" required autocomplete="email">
                <div class="email-suggestions" id="loginEmailSuggestions"></div>
            </div>
            <div class="form-group">
                <label for="loginPassword">Пароль</label>
                <input type="password" id="loginPassword" name="password" placeholder="••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn auth-submit">Войти</button>
            <p class="auth-note">Нет аккаунта? <a href="#" class="switch-to-register" role="button">Зарегистрируйтесь</a></p>
        </form>
        
        <!-- Форма регистрации -->
        <form id="registerForm" class="auth-form" aria-label="Форма регистрации">
            <div class="form-group">
                <label for="regName">Ваше имя</label>
                <input type="text" id="regName" name="full_name" placeholder="Иван Иванов" required autocomplete="name">
                <small>Только буквы и пробелы</small>
            </div>
            <div class="form-group">
                <label for="regEmail">Email</label>
                <input type="email" id="regEmail" name="email" placeholder="your@email.com" required autocomplete="email">
                <div class="email-suggestions" id="regEmailSuggestions"></div>
            </div>
            <div class="form-group">
                <label for="regPhone">Телефон</label>
                <input type="tel" id="regPhone" name="phone" placeholder="+7 (___) ___-__-__" required autocomplete="tel">
            </div>
            <div class="form-group">
                <label for="regPassword">Пароль</label>
                <input type="password" id="regPassword" name="password" placeholder="минимум 6 символов" required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="regPasswordConfirm">Подтвердите пароль</label>
                <input type="password" id="regPasswordConfirm" name="password_confirm" placeholder="••••••" required minlength="6" autocomplete="new-password">
            </div>
            <button type="submit" class="btn auth-submit">Зарегистрироваться</button>
            <p class="auth-note">Уже есть аккаунт? <a href="#" class="switch-to-login" role="button">Войдите</a></p>
        </form>
    </div>
</div>

<!-- Модальное окно оформления заказа -->
<div id="checkoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Оформление заказа</h2>
            <button class="modal-close" aria-label="Закрыть">&times;</button>
        </div>
        <form id="checkoutForm" aria-label="Форма оформления заказа">
            <div class="form-group">
                <label for="deliveryAddress">Адрес доставки</label>
                <textarea id="deliveryAddress" name="address" rows="3" placeholder="Город, улица, дом, квартира" required></textarea>
            </div>
            <div class="form-group">
                <label for="paymentMethod">Способ оплаты</label>
                <select id="paymentMethod" name="payment_method">
                    <option value="card">Банковская карта онлайн</option>
                    <option value="cash">Наличными при получении</option>
                    <option value="sbp">СБП (Система быстрых платежей)</option>
                </select>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Подтвердить заказ</button>
        </form>
    </div>
</div>

<style>
/* Стили для корзины и модальных окон */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
}

.cart-items {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.cart-header {
    display: grid;
    grid-template-columns: 100px 1fr 150px 120px 50px;
    gap: 15px;
    padding: 15px 20px;
    background: #F8F9FC;
    font-weight: 600;
    border-bottom: 1px solid #E1E8F0;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr 150px 120px 50px;
    gap: 15px;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #E1E8F0;
    transition: all 0.3s;
}

.cart-item:hover {
    background: #F8F9FC;
}

.cart-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 12px;
}

.cart-item h3 {
    font-size: 16px;
    margin-bottom: 5px;
}

.cart-item p {
    color: #5B6E8C;
    font-size: 12px;
}

.price {
    font-size: 18px;
    font-weight: 700;
    color: #5F9EF0;
}

.quantity-control {
    display: flex;
    gap: 10px;
    align-items: center;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #E1E8F0;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: #5F9EF0;
    color: white;
}

.remove-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #dc3545;
    transition: transform 0.2s;
}

.remove-btn:hover {
    transform: scale(1.1);
}

.cart-summary {
    background: white;
    padding: 30px;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.summary-row.total {
    font-size: 24px;
    font-weight: 700;
    margin-top: 20px;
}

.summary-row.total span:last-child {
    color: #5F9EF0;
}

.continue-link {
    display: block;
    text-align: center;
    margin-top: 15px;
    color: #5F9EF0;
    text-decoration: none;
}

.empty-cart {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 24px;
}

.empty-cart-icon {
    margin-bottom: 20px;
    color: #5B6E8C;
}

/* Модальные окна */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 32px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #E1E8F0;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
}

.modal-close {
    font-size: 32px;
    background: none;
    border: none;
    cursor: pointer;
    color: #5B6E8C;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #dc3545;
}

.auth-tabs {
    display: flex;
    border-bottom: 1px solid #E1E8F0;
}

.tab-btn {
    flex: 1;
    padding: 15px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.2s;
}

.tab-btn.active {
    color: #5F9EF0;
    border-bottom: 2px solid #5F9EF0;
}

.auth-form {
    display: none;
    padding: 24px;
}

.auth-form.active {
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E1E8F0;
    border-radius: 16px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #5F9EF0;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #5B6E8C;
    font-size: 12px;
}

.email-suggestions {
    display: flex;
    gap: 8px;
    margin-top: 8px;
    flex-wrap: wrap;
}

.email-suggestion {
    padding: 4px 12px;
    background: #E1E8F0;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.email-suggestion:hover {
    background: #5F9EF0;
    color: white;
}

.auth-submit {
    width: 100%;
    padding: 14px;
    font-size: 16px;
}

.auth-note {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
}

.auth-note a {
    color: #5F9EF0;
    text-decoration: none;
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-header {
        display: none;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .cart-item .price {
        grid-column: 2;
        grid-row: 2;
    }
    
    .quantity-control {
        grid-column: 2;
        grid-row: 3;
    }
    
    .remove-btn {
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .cart-item {
        position: relative;
    }
}
</style>

<script>
// Маска для телефона
function phoneMask(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    
    let formatted = '+7';
    if (value.length > 1) {
        formatted += ' (' + value.slice(1, 4);
    }
    if (value.length >= 5) {
        formatted += ') ' + value.slice(4, 7);
    }
    if (value.length >= 8) {
        formatted += '-' + value.slice(7, 9);
    }
    if (value.length >= 10) {
        formatted += '-' + value.slice(9, 11);
    }
    
    input.value = formatted;
}

// Подсказки для email
function setupEmailSuggestions(emailInput, suggestionsContainer) {
    emailInput.addEventListener('input', function(e) {
        const value = e.target.value;
        const atIndex = value.indexOf('@');
        
        if (atIndex === -1 || atIndex === value.length - 1) {
            suggestionsContainer.innerHTML = '';
            return;
        }
        
        const domains = ['yandex.ru', 'mail.ru', 'gmail.com', 'bk.ru', 'list.ru'];
        const prefix = value.slice(0, atIndex);
        
        suggestionsContainer.innerHTML = domains.map(domain => 
            `<span class="email-suggestion" data-domain="${domain}">@${domain}</span>`
        ).join('');
        
        document.querySelectorAll('.email-suggestion').forEach(suggestion => {
            suggestion.addEventListener('click', function() {
                emailInput.value = prefix + '@' + this.dataset.domain;
                suggestionsContainer.innerHTML = '';
            });
        });
    });
}

// Модальные окна
const authModal = document.getElementById('authModal');
const checkoutModal = document.getElementById('checkoutModal');

function openModal(modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Закрытие модалок
document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', function() {
        closeModal(this.closest('.modal'));
    });
});

// Клик вне модалки для закрытия
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target);
    }
});

// Переключение между формами
document.querySelectorAll('.switch-to-register').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.tab-btn[data-tab="register"]').click();
    });
});

document.querySelectorAll('.switch-to-login').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.tab-btn[data-tab="login"]').click();
    });
});

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;
        
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
        document.getElementById(`${tab}Form`).classList.add('active');
        
        document.getElementById('modalTitle').textContent = tab === 'login' ? 'Вход в аккаунт' : 'Создание аккаунта';
    });
});

// Телефон маска
const phoneInput = document.getElementById('regPhone');
if (phoneInput) {
    phoneInput.addEventListener('input', function() {
        phoneMask(this);
    });
}

// Email подсказки
setupEmailSuggestions(document.getElementById('loginEmail'), document.getElementById('loginEmailSuggestions'));
setupEmailSuggestions(document.getElementById('regEmail'), document.getElementById('regEmailSuggestions'));

// Валидация имени (только буквы)
const nameInput = document.getElementById('regName');
if (nameInput) {
    nameInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-zА-Яа-я\s]/g, '');
    });
}

// Регистрация
document.getElementById('registerForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const phone = document.getElementById('regPhone').value;
    const password = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regPasswordConfirm').value;
    
    if (password !== confirm) {
        alert('Пароли не совпадают');
        return;
    }
    
    if (password.length < 6) {
        alert('Пароль должен быть не менее 6 символов');
        return;
    }
    
    try {
        const response = await fetch('/ajax_auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=register&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&password=${encodeURIComponent(password)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal(authModal);
            openCheckoutModal();
        } else {
            alert(data.error);
        }
    } catch(error) {
        alert('Ошибка при регистрации');
    }
});

// Вход
document.getElementById('loginForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    try {
        const response = await fetch('/ajax_auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal(authModal);
            openCheckoutModal();
        } else {
            alert(data.error);
        }
    } catch(error) {
        alert('Ошибка при входе');
    }
});

// Оформление заказа
document.getElementById('checkoutBtn')?.addEventListener('click', async function() {
    <?php if (!$is_logged_in): ?>
        openModal(authModal);
    <?php else: ?>
        openCheckoutModal();
    <?php endif; ?>
});

function openCheckoutModal() {
    openModal(checkoutModal);
}

document.getElementById('checkoutForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const address = document.getElementById('deliveryAddress').value;
    const payment_method = document.getElementById('paymentMethod').value;
    
    try {
        const response = await fetch('/cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=checkout&address=${encodeURIComponent(address)}&payment_method=${payment_method}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Заказ №${data.order_number} успешно оформлен!`);
            window.location.href = '/account.php';
        } else if (data.need_auth) {
            closeModal(checkoutModal);
            openModal(authModal);
        } else {
            alert('Ошибка при оформлении заказа');
        }
    } catch(error) {
        alert('Ошибка при оформлении заказа');
    }
});

// Обновление количества товаров
    document.querySelectorAll('.btn-update').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const productId = this.dataset.id;
            const change = parseInt(this.dataset.change);
            const quantitySpan = document.getElementById(`qty-${productId}`);
            let newQuantity = parseInt(quantitySpan.textContent) + change;
            
            if (newQuantity < 1) newQuantity = 1;
            
            try {
                const response = await fetch('/cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=update&id=${productId}&quantity=${newQuantity}`
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch(error) {
                console.error('Ошибка:', error);
            }
        });
    });

// Удаление товаров
document.querySelectorAll('.btn-remove').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        const productId = this.dataset.id;
        
        if (confirm('Удалить товар из корзины?')) {
            try {
                const response = await fetch('/cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=remove&id=${productId}`
                });
                
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch(error) {
                console.error('Ошибка:', error);
            }
        }
    });
});
</script>

<div id="successModal" class="modal">
    <div class="modal-content success-modal">
        <div class="success-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <h2>Заказ оформлен!</h2>
        <p>Спасибо за доверие. Номер вашего заказа: <strong id="orderNumber"></strong></p>
        <p style="color: #5B6E8C; margin-top: 8px;">Мы свяжемся с вами в ближайшее время</p>
        <button class="btn" onclick="window.location.href='/account.php'" style="margin-top: 20px;">Перейти в личный кабинет</button>
        <button class="btn" onclick="window.location.href='/catalog.php'" style="margin-top: 10px; background: #E1E8F0; color: #1E2A3E;">Продолжить покупки</button>
    </div>
</div>

<style>
.success-modal {
    text-align: center;
    padding: 40px 32px;
}
.success-icon {
    width: 80px;
    height: 80px;
    background: #DCFCE7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.success-modal h2 {
    font-family: 'Manrope', sans-serif;
    font-size: 28px;
    color: #1E2A3E;
}
.success-modal p {
    color: #5B6E8C;
    font-size: 16px;
    margin-top: 8px;
}
.success-modal .btn {
    width: 100%;
    max-width: 300px;
}
</style>

<script>
// В функции обработки заказа замените alert на модалку:
// Вместо alert(`Заказ №${data.order_number} успешно оформлен!`);
// Используйте:

if (data.success) {
    document.getElementById('orderNumber').textContent = data.order_number;
    closeModal(checkoutModal);
    openModal(document.getElementById('successModal'));
    setTimeout(() => {
        // Не редиректим сразу, даем пользователю увидеть модалку
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
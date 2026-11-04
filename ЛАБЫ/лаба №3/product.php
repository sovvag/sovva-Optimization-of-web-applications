<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/auth_functions.php';
include 'includes/wishlist_functions.php';
include 'includes/header.php';

// Получаем ID товара из URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: catalog.php');
    exit;
}

// Получаем данные товара
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: 404.php');
    exit;
}

// Получаем похожие товары (из той же категории)
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt->execute([$product['category_id'], $id]);
$similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Проверяем в избранном
$session_id = session_id();
$wishlist_items = getWishlistItems($pdo, $session_id);
$wishlist_product_ids = array_column($wishlist_items, 'product_id');
$in_wishlist = in_array($id, $wishlist_product_ids);

// Проверяем в корзине
$cart_items = getCartItems($pdo, $session_id);
$cart_product_ids = array_column($cart_items, 'product_id');
$in_cart = in_array($id, $cart_product_ids);
$cart_quantity = 0;
foreach ($cart_items as $item) {
    if ($item['product_id'] == $id) {
        $cart_quantity = $item['quantity'];
        break;
    }
}
?>

<main class="container">
    <!-- Навигация -->
    <div style="margin: 30px 0 10px;">
        <a href="catalog.php" style="color: #5F9EF0; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;" 
           onmouseover="this.style.color='#3A7BD5'" onmouseout="this.style.color='#5F9EF0'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Назад в каталог
        </a>
    </div>

    <!-- Основная информация -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin: 20px 0 50px;">
        <!-- Фото товара -->
        <div style="background: #F8F9FC; border-radius: 24px; overflow: hidden; position: sticky; top: 100px; height: fit-content;">
            <img src="/assets/images/<?= $product['image'] ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 style="width: 100%; height: 500px; object-fit: contain; padding: 20px;"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22500%22 height=%22500%22%3E%3Crect width=%22500%22 height=%22500%22 fill=%22%23F8F9FC%22/%3E%3Ctext x=%22250%22 y=%22250%22 text-anchor=%22middle%22 fill=%22%235B6E8C%22 font-size=%2248%22%3E%3F%3C/text%3E%3C/svg%3E'">
        </div>

        <!-- Информация о товаре -->
        <div>
            <!-- Метки -->
            <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
                <?php if ($product['is_new']): ?>
                    <span style="background: #DBEAFE; color: #1D4ED8; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;">Новинка</span>
                <?php endif; ?>
                <?php if ($product['is_popular']): ?>
                    <span style="background: #FEF3C7; color: #92400E; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;">Популярное</span>
                <?php endif; ?>
                <span style="background: #E1E8F0; color: #5B6E8C; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 500;">
                    <?= $product['category_name'] ?? 'Без категории' ?>
                </span>
            </div>

            <!-- Название и избранное -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                <h1 style="font-family: 'Manrope', sans-serif; font-size: 32px; margin: 0;"><?= htmlspecialchars($product['name']) ?></h1>
                <button class="wishlist-btn-product <?= $in_wishlist ? 'active' : '' ?>" 
                        data-id="<?= $product['id'] ?>"
                        style="background: none; border: none; cursor: pointer; transition: all 0.3s; padding: 4px 8px; flex-shrink: 0; color: <?= $in_wishlist ? '#FF6B6B' : '#5B6E8C' ?>;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="<?= $in_wishlist ? '#FF6B6B' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </button>
            </div>

            <!-- Цена -->
            <div style="font-size: 36px; font-weight: 700; color: #5F9EF0; margin: 20px 0 15px;">
                <?= number_format($product['price'], 0, ',', ' ') ?> ₽
            </div>

            <!-- Наличие -->
            <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: center;">
                <span style="color: #5B6E8C; font-size: 14px;">Наличие:</span>
                <?php if ($product['stock'] > 0): ?>
                    <span style="color: #22C55E; font-weight: 600;">В наличии (<?= $product['stock'] ?> шт.)</span>
                <?php else: ?>
                    <span style="color: #DC2626; font-weight: 600;">Нет в наличии</span>
                <?php endif; ?>
            </div>

            <!-- Описание -->
            <div style="background: #F8F9FC; padding: 20px 24px; border-radius: 16px; margin: 20px 0;">
                <h3 style="font-family: 'Manrope', sans-serif; font-size: 16px; margin-bottom: 10px; color: #1E2A3E;">Описание</h3>
                <p style="color: #5B6E8C; line-height: 1.8; margin: 0;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>

            <!-- Действия -->
            <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
                <?php if ($product['stock'] > 0): ?>
                    <?php if ($in_cart): ?>
                        <div style="display: flex; align-items: center; gap: 12px; background: #F8F9FC; padding: 4px 16px; border-radius: 40px; border: 2px solid #E1E8F0;">
                            <button class="qty-btn update-cart-btn" data-id="<?= $product['id'] ?>" data-change="-1" 
                                    style="width: 36px; height: 36px; border: none; background: #E1E8F0; border-radius: 50%; font-size: 20px; cursor: pointer; transition: all 0.2s;">
                                −
                            </button>
                            <span id="cart-qty-<?= $product['id'] ?>" style="font-weight: 600; font-size: 18px; min-width: 30px; text-align: center;">
                                <?= $cart_quantity ?>
                            </span>
                            <button class="qty-btn update-cart-btn" data-id="<?= $product['id'] ?>" data-change="1" 
                                    style="width: 36px; height: 36px; border: none; background: #E1E8F0; border-radius: 50%; font-size: 20px; cursor: pointer; transition: all 0.2s;">
                                +
                            </button>
                        </div>
                    <?php else: ?>
                        <button class="btn add-to-cart" data-id="<?= $product['id'] ?>" 
                                style="padding: 14px 40px; font-size: 16px; display: inline-flex; align-items: center; gap: 10px; background: #5F9EF0; color: white; border: none; border-radius: 40px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            В корзину
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn" disabled style="background: #E1E8F0; color: #5B6E8C; cursor: not-allowed; padding: 14px 40px; font-size: 16px; border: none; border-radius: 40px;">
                        Нет в наличии
                    </button>
                <?php endif; ?>
                
                <button class="btn wishlist-btn-product <?= $in_wishlist ? 'active' : '' ?>" 
                        data-id="<?= $product['id'] ?>"
                        style="padding: 14px 24px; background: <?= $in_wishlist ? '#FF6B6B' : '#E1E8F0' ?>; color: <?= $in_wishlist ? 'white' : '#5B6E8C' ?>; border: none; border-radius: 40px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $in_wishlist ? 'white' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <?= $in_wishlist ? 'В избранном' : 'В избранное' ?>
                </button>
            </div>

            <!-- Характеристики -->
            <div style="margin-top: 35px; padding-top: 25px; border-top: 1px solid #E1E8F0;">
                <h3 style="font-family: 'Manrope', sans-serif; font-size: 16px; margin-bottom: 15px; color: #1E2A3E;">Характеристики</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: #F8F9FC; padding: 10px 16px; border-radius: 12px;">
                        <span style="color: #5B6E8C; font-size: 12px; display: block;">Артикул</span>
                        <p style="font-weight: 600; margin: 4px 0 0;">#<?= $product['id'] ?></p>
                    </div>
                    <div style="background: #F8F9FC; padding: 10px 16px; border-radius: 12px;">
                        <span style="color: #5B6E8C; font-size: 12px; display: block;">Категория</span>
                        <p style="font-weight: 600; margin: 4px 0 0;"><?= $product['category_name'] ?? 'Без категории' ?></p>
                    </div>
                    <div style="background: #F8F9FC; padding: 10px 16px; border-radius: 12px;">
                        <span style="color: #5B6E8C; font-size: 12px; display: block;">Наличие</span>
                        <p style="font-weight: 600; margin: 4px 0 0; color: <?= $product['stock'] > 0 ? '#22C55E' : '#DC2626' ?>;">
                            <?= $product['stock'] > 0 ? 'В наличии' : 'Нет в наличии' ?>
                        </p>
                    </div>
                    <div style="background: #F8F9FC; padding: 10px 16px; border-radius: 12px;">
                        <span style="color: #5B6E8C; font-size: 12px; display: block;">Остаток</span>
                        <p style="font-weight: 600; margin: 4px 0 0;"><?= $product['stock'] ?> шт.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Похожие товары -->
    <?php if (count($similar_products) > 0): ?>
        <section style="margin: 60px 0 40px;">
            <h2 style="font-family: 'Manrope', sans-serif; font-size: 28px; margin-bottom: 30px;">Похожие товары</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">
                <?php foreach($similar_products as $similar): ?>
                <div class="product-card" onclick="window.location.href='product.php?id=<?= $similar['id'] ?>'" 
                     style="cursor: pointer; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s; border: 1px solid #F0F4FF;">
                    <img src="/assets/images/<?= $similar['image'] ?>" 
                         alt="<?= htmlspecialchars($similar['name']) ?>"
                         style="width: 100%; height: 180px; object-fit: cover;"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22180%22%3E%3Crect width=%22200%22 height=%22180%22 fill=%22%23F8F9FC%22/%3E%3C/svg%3E'">
                    <div style="padding: 16px;">
                        <h4 style="font-size: 14px; margin: 0 0 6px; color: #1E2A3E;"><?= htmlspecialchars($similar['name']) ?></h4>
                        <div style="font-size: 18px; font-weight: 700; color: #5F9EF0;"><?= number_format($similar['price'], 0, ',', ' ') ?> ₽</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<style>
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(95,158,240,0.15) !important;
        border-color: #5F9EF0 !important;
    }
    .wishlist-btn-product {
        transition: all 0.3s ease;
    }
    .wishlist-btn-product:hover {
        transform: scale(1.15);
    }
    .wishlist-btn-product.active {
        color: #FF6B6B;
    }
    .qty-btn:hover {
        background: #5F9EF0 !important;
        color: white !important;
    }
    .add-to-cart:hover {
        background: #3A7BD5 !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(95,158,240,0.3);
    }
    @media (max-width: 768px) {
        .product-page-grid {
            grid-template-columns: 1fr;
        }
        .product-page-grid .sticky {
            position: static;
        }
        .product-page-grid img {
            height: 300px !important;
        }
    }
</style>

<script>
// Добавление/удаление из избранного
document.querySelectorAll('.wishlist-btn-product').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        const productId = this.dataset.id;
        const isActive = this.classList.contains('active');
        
        const response = await fetch('/ajax_wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=${isActive ? 'remove' : 'add'}&product_id=${productId}`
        });
        
        if (response.ok) {
            this.classList.toggle('active');
            // Обновляем обе кнопки
            document.querySelectorAll('.wishlist-btn-product').forEach(b => {
                if (b.dataset.id == productId) {
                    b.classList.toggle('active');
                    const isNowActive = b.classList.contains('active');
                    // Обновляем SVG в кнопке
                    const svg = b.querySelector('svg');
                    if (svg) {
                        svg.setAttribute('fill', isNowActive ? '#FF6B6B' : 'none');
                    }
                    // Обновляем текст
                    const textNode = b.childNodes[b.childNodes.length - 1];
                    if (textNode && textNode.textContent) {
                        textNode.textContent = isNowActive ? ' В избранном' : ' В избранное';
                    }
                    b.style.background = isNowActive ? '#FF6B6B' : '#E1E8F0';
                    b.style.color = isNowActive ? 'white' : '#5B6E8C';
                    
                    // Обновляем сердечко рядом с названием
                    document.querySelectorAll('.wishlist-btn-product:not(.btn)').forEach(heart => {
                        if (heart.dataset.id == productId) {
                            const heartSvg = heart.querySelector('svg');
                            if (heartSvg) {
                                heartSvg.setAttribute('fill', isNowActive ? '#FF6B6B' : 'none');
                            }
                            heart.style.color = isNowActive ? '#FF6B6B' : '#5B6E8C';
                        }
                    });
                }
            });
            
            // Обновляем счетчик в хедере
            const counter = document.querySelector('.wishlist-icon .count-badge');
            if (counter) {
                let count = parseInt(counter.textContent) || 0;
                count = isActive ? count - 1 : count + 1;
                counter.textContent = count;
            }
        }
    });
});

// Обновление количества в корзине
document.querySelectorAll('.update-cart-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        const productId = this.dataset.id;
        const change = parseInt(this.dataset.change);
        const quantitySpan = document.getElementById(`cart-qty-${productId}`);
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
                quantitySpan.textContent = newQuantity;
                // Обновляем счетчик в хедере
                const counter = document.querySelector('.cart-icon .count-badge');
                if (counter) {
                    counter.textContent = data.count;
                }
            }
        } catch(error) {
            console.error('Ошибка:', error);
        }
    });
});

// Добавление в корзину
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        const productId = this.dataset.id;
        
        try {
            const response = await fetch('/cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=add&id=${productId}`
            });
            
            const data = await response.json();
            if (data.success) {
                // Обновляем счетчик
                const counter = document.querySelector('.cart-icon .count-badge');
                if (counter) {
                    counter.textContent = data.count;
                }
                showNotification('Товар добавлен в корзину');
                setTimeout(() => location.reload(), 500);
            }
        } catch(error) {
            console.error('Ошибка:', error);
        }
    });
});

function showNotification(message) {
    const notif = document.createElement('div');
    notif.textContent = message;
    notif.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #5F9EF0;
        color: white;
        padding: 12px 24px;
        border-radius: 40px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        font-weight: 500;
        box-shadow: 0 4px 20px rgba(95,158,240,0.3);
        font-family: 'Inter', sans-serif;
    `;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);
}

// Добавляем стиль для анимации
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>
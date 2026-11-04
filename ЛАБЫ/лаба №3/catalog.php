<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/wishlist_functions.php';
include 'includes/header.php';

$session_id = session_id();
$wishlist_items = getWishlistItems($pdo, $session_id);
$wishlist_product_ids = array_column($wishlist_items, 'product_id');
?>

<main class="container">
    <h1 style="margin: 40px 0 20px;">Каталог товаров</h1>
    
    <!-- Фильтр по категориям -->
    <div style="display: flex; gap: 15px; margin-bottom: 40px; flex-wrap: wrap;">
        <a href="/catalog.php" class="btn" style="background: <?= !isset($_GET['cat']) ? '#5F9EF0' : '#E1E8F0' ?>; color: <?= !isset($_GET['cat']) ? 'white' : '#333' ?>">Все</a>
        <a href="?cat=glasses" class="btn" style="background: <?= isset($_GET['cat']) && $_GET['cat'] == 'glasses' ? '#5F9EF0' : '#E1E8F0' ?>; color: <?= isset($_GET['cat']) && $_GET['cat'] == 'glasses' ? 'white' : '#333' ?>">Очки</a>
        <a href="?cat=sunglasses" class="btn" style="background: <?= isset($_GET['cat']) && $_GET['cat'] == 'sunglasses' ? '#5F9EF0' : '#E1E8F0' ?>; color: <?= isset($_GET['cat']) && $_GET['cat'] == 'sunglasses' ? 'white' : '#333' ?>">Солнцезащитные очки</a>
        <a href="?cat=contacts" class="btn" style="background: <?= isset($_GET['cat']) && $_GET['cat'] == 'contacts' ? '#5F9EF0' : '#E1E8F0' ?>; color: <?= isset($_GET['cat']) && $_GET['cat'] == 'contacts' ? 'white' : '#333' ?>">Контактные линзы</a>
    </div>
    
    <div class="products-grid">
        <?php
        // Получаем товары из БД (только glasses, sunglasses, contacts)
        $sql = "SELECT * FROM products WHERE category_id IN (1, 2, 4)";
        if (isset($_GET['cat'])) {
            $cat = $_GET['cat'];
            if ($cat == 'glasses') $sql .= " AND category_id = 1";
            if ($cat == 'contacts') $sql .= " AND category_id = 2";
            if ($cat == 'sunglasses') $sql .= " AND category_id = 4";
        }
        
        $stmt = $pdo->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0):
            foreach($products as $product):
                $in_wishlist = in_array($product['id'], $wishlist_product_ids);
        ?>
            <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                <img src="/assets/images/<?= $product['image'] ?>" 
                     onerror="this.src='https://placehold.co/300x200/5F9EF0/white?text='+encodeURIComponent('<?= $product['name'] ?>')">
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p style="color: #5B6E8C; font-size: 14px;"><?= mb_substr($product['description'], 0, 80) ?>...</p>
                    <div class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                    <div class="product-actions">
                        <button class="action-btn cart-btn add-to-cart" data-id="<?= $product['id'] ?>" onclick="event.stopPropagation();">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            В корзину
                        </button>
                        <button class="action-btn wishlist-btn <?= $in_wishlist ? 'active' : '' ?>" 
                                data-id="<?= $product['id'] ?>" onclick="event.stopPropagation();">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $in_wishlist ? '#FF6B6B' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <p>Товары не найдены</p>
        <?php endif; ?>
    </div>
</main>

<style>
    .product-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(95,158,240,0.15);
    }
    .product-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    .action-btn {
        padding: 10px 16px;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
    }
    .cart-btn {
        flex: 1;
        background: #E1E8F0;
        color: #5B6E8C;
    }
    .cart-btn:hover {
        background: #5F9EF0;
        color: white;
    }
    .wishlist-btn {
        width: 44px;
        justify-content: center;
        background: #E1E8F0;
        color: #5B6E8C;
        font-size: 18px;
    }
    .wishlist-btn:hover {
        background: #FF6B6B;
        color: white;
    }
    .wishlist-btn.active {
        background: #FF6B6B;
        color: white;
    }
    .wishlist-btn.active svg {
        fill: #FF6B6B;
    }
    .price {
        font-size: 22px;
        font-weight: 700;
        color: #5F9EF0;
        margin: 8px 0;
    }
</style>

<script>
// AJAX для добавления/удаления из избранного
document.querySelectorAll('.wishlist-btn').forEach(btn => {
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
            const nowActive = this.classList.contains('active');
            const svg = this.querySelector('svg');
            if (svg) {
                svg.setAttribute('fill', nowActive ? '#FF6B6B' : 'none');
            }
            this.style.background = nowActive ? '#FF6B6B' : '#E1E8F0';
            this.style.color = nowActive ? 'white' : '#5B6E8C';
            
            // Обновляем счетчик в хедере
            const counter = document.querySelector('.wishlist-icon .count-badge');
            if (counter) {
                let count = parseInt(counter.textContent) || 0;
                count = nowActive ? count + 1 : count - 1;
                counter.textContent = count;
            }
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
                const counter = document.querySelector('.cart-icon .count-badge');
                if (counter) {
                    counter.textContent = data.count;
                }
                showNotification('Товар добавлен в корзину');
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
<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/wishlist_functions.php';
include 'includes/header.php';

$session_id = session_id();
$current_category = $_GET['category'] ?? null;
$wishlist_items = getWishlistItems($pdo, $session_id, $current_category);
$categories = getWishlistCategories($pdo, $session_id);
?>

<main class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin: 40px 0 20px; flex-wrap: wrap; gap: 15px;">
        <h1 style="margin: 0;">Моё избранное</h1>
        <span style="color: #5B6E8C; font-size: 14px; background: #F8F9FC; padding: 6px 16px; border-radius: 20px;">
            <?= count($wishlist_items) ?> товаров
        </span>
    </div>
    
    <!-- Фильтр по категориям -->
    <?php if (!empty($categories)): ?>
    <div style="display: flex; gap: 12px; margin-bottom: 30px; flex-wrap: wrap; align-items: center;">
        <a href="wishlist.php" class="btn" style="background: <?= !$current_category ? '#5F9EF0' : '#E1E8F0' ?>; color: <?= !$current_category ? 'white' : '#333' ?>; padding: 8px 20px; border-radius: 40px; text-decoration: none; font-weight: 500; transition: all 0.2s; font-size: 14px;">
            Все
        </a>
        <?php foreach($categories as $cat): ?>
        <a href="?category=<?= urlencode($cat) ?>" class="btn" style="background: <?= $current_category == $cat ? '#5F9EF0' : '#E1E8F0' ?>; color: <?= $current_category == $cat ? 'white' : '#333' ?>; padding: 8px 20px; border-radius: 40px; text-decoration: none; font-weight: 500; transition: all 0.2s; font-size: 14px;">
            <?= htmlspecialchars($cat) ?>
        </a>
        <?php endforeach; ?>
        
        <button onclick="createCategory()" style="background: #22C55E; color: white; border: none; padding: 8px 20px; border-radius: 40px; cursor: pointer; font-weight: 500; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Новая категория
        </button>
    </div>
    <?php endif; ?>
    
    <?php if (count($wishlist_items) > 0): ?>
        <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
            <?php foreach($wishlist_items as $item): ?>
            <div class="product-card" data-product-id="<?= $item['product_id'] ?>" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s; border: 1px solid #F0F4FF; position: relative;">
                <div style="position: relative;">
                    <img src="/assets/images/<?= $item['image'] ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>"
                         style="width: 100%; height: 220px; object-fit: cover;"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect width=%22300%22 height=%22200%22 fill=%22%23F8F9FC%22/%3E%3Ctext x=%22150%22 y=%22110%22 text-anchor=%22middle%22 fill=%22%235B6E8C%22 font-size=%2236%22%3E%3F%3C/text%3E%3C/svg%3E'">
                    <span style="position: absolute; top: 12px; right: 12px; background: #FF6B6B; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 500;">
                        <?= htmlspecialchars($item['category_name']) ?>
                    </span>
                </div>
                <div class="product-info" style="padding: 20px;">
                    <h3 style="font-size: 16px; margin: 0 0 6px;"><?= htmlspecialchars($item['name']) ?></h3>
                    <p style="color: #5B6E8C; font-size: 13px; line-height: 1.5; margin: 0 0 10px;"><?= mb_substr($item['description'], 0, 60) ?>...</p>
                    <div class="price" style="font-size: 22px; font-weight: 700; color: #5F9EF0; margin: 8px 0 15px;"><?= number_format($item['price'], 0, ',', ' ') ?> ₽</div>
                    
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button class="move-to-cart" data-id="<?= $item['product_id'] ?>" 
                                style="flex: 1; background: #5F9EF0; color: white; border: none; padding: 10px 16px; border-radius: 40px; cursor: pointer; font-weight: 500; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            В корзину
                        </button>
                        <button onclick="changeCategory(<?= $item['product_id'] ?>, '<?= htmlspecialchars($item['category_name']) ?>')" 
                                style="background: #F0F4FF; color: #5B6E8C; border: none; padding: 10px 14px; border-radius: 40px; cursor: pointer; font-weight: 500; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                        </button>
                        <button class="remove-from-wishlist" data-id="<?= $item['product_id'] ?>" 
                                style="background: #FEE2E2; color: #DC2626; border: none; padding: 10px 14px; border-radius: 40px; cursor: pointer; font-weight: 500; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 80px 40px; background: white; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
            <div style="width: 80px; height: 80px; background: #F0F4FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #5B6E8C;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </div>
            <h3 style="font-family: 'Manrope', sans-serif; font-size: 24px; margin: 0 0 10px;">Ваше избранное пусто</h3>
            <p style="color: #5B6E8C; margin: 0 0 30px;">Добавляйте товары в избранное, чтобы не потерять понравившиеся позиции</p>
            <a href="catalog.php" class="btn" style="display: inline-block; padding: 12px 32px; background: #5F9EF0; color: white; border-radius: 40px; text-decoration: none; font-weight: 600; transition: all 0.2s;">Перейти в каталог</a>
        </div>
    <?php endif; ?>
</main>

<style>
    .product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(95,158,240,0.12) !important;
        border-color: #5F9EF0 !important;
    }
    .move-to-cart:hover {
        background: #3A7BD5 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(95,158,240,0.3);
    }
    .remove-from-wishlist:hover {
        background: #FECACA !important;
        transform: scale(1.05);
    }
    .btn-category:hover {
        background: #5F9EF0 !important;
        color: white !important;
    }
    .btn-category.active {
        background: #5F9EF0 !important;
        color: white !important;
    }
</style>

<script>
// Функция создания новой категории
function createCategory() {
    const categoryName = prompt('Введите название новой категории (например: "Подарок на ДР" или "Куплю позже"):');
    if (categoryName && categoryName.trim()) {
        localStorage.setItem('new_category', categoryName);
        showNotification('Категория "' + categoryName + '" создана!');
    }
}

// Функция изменения категории
function changeCategory(productId, currentCategory) {
    const categories = <?= json_encode($categories) ?>;
    const newCategoryFromStorage = localStorage.getItem('new_category');
    
    let options = categories.join(', ');
    if (newCategoryFromStorage && !categories.includes(newCategoryFromStorage)) {
        options += ', ' + newCategoryFromStorage;
    }
    
    const newCategory = prompt('Введите название категории\nДоступные: ' + options + '\nИли введите новую:', currentCategory);
    
    if (newCategory && newCategory !== currentCategory) {
        fetch('ajax_wishlist.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=update_category&product_id=${productId}&category=${encodeURIComponent(newCategory)}`
        }).then(() => {
            location.reload();
        });
    }
}

// Перемещение в корзину
document.querySelectorAll('.move-to-cart').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const productId = btn.dataset.id;
        
        try {
            const response = await fetch('ajax_wishlist.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=move_to_cart&product_id=${productId}`
            });
            
            if (response.ok) {
                showNotification('Товар перемещён в корзину');
                setTimeout(() => location.reload(), 500);
            }
        } catch(error) {
            console.error('Ошибка:', error);
        }
    });
});

// Удаление из избранного
document.querySelectorAll('.remove-from-wishlist').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        if (confirm('Удалить товар из избранного?')) {
            const productId = btn.dataset.id;
            
            try {
                const response = await fetch('ajax_wishlist.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=remove&product_id=${productId}`
                });
                
                if (response.ok) {
                    showNotification('Товар удалён из избранного');
                    setTimeout(() => location.reload(), 500);
                }
            } catch(error) {
                console.error('Ошибка:', error);
            }
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
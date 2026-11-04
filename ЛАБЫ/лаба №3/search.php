<?php
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/header.php';

$search_query = $_GET['q'] ?? '';
$products = [];

if ($search_query) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $stmt->execute(["%$search_query%", "%$search_query%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="container">
    <h1 style="margin: 40px 0;">🔍 Результаты поиска: "<?= htmlspecialchars($search_query) ?>"</h1>
    
    <?php if ($search_query): ?>
        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                    <img src="/assets/images/<?= $product['image'] ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22200%22%3E%3Crect width=%22300%22 height=%22200%22 fill=%225F9EF0%22/%3E%3C/svg%3E'">
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                        <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">В корзину</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; background: #F8F9FC; border-radius: 24px;">
                <p style="font-size: 18px; color: #5B6E8C;">Товары не найдены</p>
                <a href="/catalog.php" class="btn" style="margin-top: 20px;">Посмотреть каталог</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: #F8F9FC; border-radius: 24px;">
            <p style="font-size: 18px; color: #5B6E8C;">Введите поисковый запрос</p>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
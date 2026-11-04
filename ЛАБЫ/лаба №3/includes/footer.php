<?php
// Определяем корневой путь
$root_path = '';
if (strpos($_SERVER['REQUEST_URI'], '/includes/') !== false) {
    $root_path = '../';
} elseif (strpos($_SERVER['REQUEST_URI'], '/admin_') !== false) {
    $root_path = '../';
} else {
    $root_path = '';
}
?>
<footer style="background: #F8F9FC; padding: 60px 0 30px; margin-top: 80px;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px;">
            <div>
                <h4 style="font-family: 'Manrope'; margin-bottom: 20px;">Lens & Look</h4>
                <p style="color: #5B6E8C;">Заботимся о вашем зрении с 2024 года</p>
            </div>
            <div>
                <h4 style="font-family: 'Manrope'; margin-bottom: 20px;">Покупателям</h4>
                <ul style="list-style: none;">
                    <li><a href="<?= $root_path ?>delivery.php" style="text-decoration: none; color: #5B6E8C;">Доставка</a></li>
                    <li><a href="<?= $root_path ?>payment.php" style="text-decoration: none; color: #5B6E8C;">Оплата</a></li>
                    <li><a href="<?= $root_path ?>returns.php" style="text-decoration: none; color: #5B6E8C;">Возврат</a></li>
                </ul>
            </div>
            <div>
                <h4 style="font-family: 'Manrope'; margin-bottom: 20px;">Контакты</h4>
                <p style="color: #5B6E8C;">8-800-555-01-01</p>
                <p style="color: #5B6E8C;">info@lenslook.ru</p>
            </div>
        </div>
        <hr style="border-color: #E1E8F0;">
        <p style="text-align: center; color: #5B6E8C; margin-top: 30px;">© 2024 Lens & Look. Все права защищены.</p>
    </div>
</footer>
<script src="<?= $root_path ?>assets/js/main.js"></script>
</body>
</html>
-- ============================================
-- 1. ТАБЛИЦА КАТЕГОРИЙ
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INT DEFAULT 0,
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 2. ТАБЛИЦА ТОВАРОВ
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT DEFAULT NULL,
    is_new TINYINT(1) DEFAULT 0,
    is_popular TINYINT(1) DEFAULT 0,
    stock INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_new (is_new),
    INDEX idx_popular (is_popular),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 3. ТАБЛИЦА ПОЛЬЗОВАТЕЛЕЙ
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150),
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    address TEXT,
    city VARCHAR(100),
    reset_token VARCHAR(255) NULL,
    is_guest TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 4. ТАБЛИЦА КОРЗИНЫ
-- ============================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    product_id INT DEFAULT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    INDEX idx_product (product_id),
    UNIQUE KEY unique_cart_user (user_id, product_id),
    UNIQUE KEY unique_cart_session (session_id, product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 5. ТАБЛИЦА ИЗБРАННОГО
-- ============================================
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    category_name VARCHAR(100) DEFAULT 'Основной',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (session_id, product_id, category_name),
    INDEX idx_session (session_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 6. ТАБЛИЦА ЗАКАЗОВ
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('new', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'new',
    payment_method VARCHAR(50) DEFAULT 'card',
    delivery_address TEXT,
    delivery_price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 7. ТАБЛИЦА ТОВАРОВ В ЗАКАЗЕ
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 8. ЗАПОЛНЯЕМ КАТЕГОРИИ
-- ============================================
INSERT INTO categories (id, name, parent_id, slug, sort_order) VALUES
(1, 'Очки', NULL, 'glasses', 1),
(2, 'Контактные линзы', NULL, 'contacts', 2),
(4, 'Солнцезащитные очки', NULL, 'sunglasses', 3);

-- ============================================
-- 9. ЗАПОЛНЯЕМ ТОВАРЫ
-- ============================================

-- Очки (category_id = 1)
INSERT INTO products (id, name, description, price, image, category_id, is_new, is_popular, stock) VALUES
(1, 'Очки для зрения "Элегант"', 'Лёгкие, прочные, с антибликовым покрытием', 4990.00, 'glasses1.jpg', 1, 1, 1, 10),
(6, 'Очки для зрения "Комфорт Плюс"', 'Очки для зрения "Комфорт Плюс" - идеальный выбор для ежедневного ношения. Изготовлены из легкого и гипоаллергенного материала, обеспечивают максимальный комфорт даже при длительном использовании. Антибликовое покрытие защищает от бликов мониторов и фар автомобилей, снижая усталость глаз. Оправа из высокопрочного пластика с металлическими вставками придает элегантный вид и гарантирует долговечность.', 14990.00, '3angle2.jpg', 1, 1, 1, 15),
(7, 'Очки для работы за компьютером "Office Pro"', 'Очки для работы за компьютером "Office Pro" созданы специально для офисных сотрудников и программистов. Специальное покрытие блокирует 40% синего света от экранов, предотвращая усталость и сухость глаз. Ультралегкая оправа весом всего 18 грамм практически не ощущается на лице. Линзы с улучшенной светопропускной способностью обеспечивают четкое зрение при любом освещении. Подходят для коррекции близорукости и дальнозоркости.', 6990.00, 'glasses_office.jpg', 1, 1, 1, 8),
(8, 'Элитные очки для зрения "Titanium Luxe"', 'Элитные очки для зрения "Titanium Luxe" из медицинского титана - воплощение надежности и стиля. Оправа гипоаллергенна, не окисляется и не темнеет со временем. Линзы с мульти-покрытием (антиблик, грязеотталкивающее, олеофобное) обеспечивают кристально чистое зрение в любых условиях. Усиленные дужки с силиконовыми наконечниками предотвращают сползание. Подходят для активного образа жизни и спорта.', 12990.00, 'glasses_titanium.jpg', 1, 0, 1, 5);

-- Контактные линзы (category_id = 2)
INSERT INTO products (id, name, description, price, image, category_id, is_new, is_popular, stock) VALUES
(3, 'Контактные линзы "AirSoft"', 'Дышащие, увлажнённые, 30 дней', 1290.00, 'contacts1.jpg', 2, 1, 1, 10),
(12, 'Acuvue Oasys 1-Day', 'Ежедневные линзы с увлажнением на весь день', 1890.00, 'acuvue_oasys.jpg', 2, 1, 1, 100),
(13, 'Biofinity Energys', 'Линзы для работы за компьютером', 1650.00, 'biofinity_energys.jpg', 2, 1, 0, 85),
(14, 'Dailies Total 1', 'Максимальное увлажнение на 100%', 2100.00, 'dailies_total1.jpg', 2, 0, 1, 120),
(15, 'Air Optix Aqua', 'Дышащие линзы на месяц', 1450.00, 'air_optix_aqua.jpg', 2, 0, 1, 95),
(16, 'PureVision 2 HD', 'Чёткое зрение в любом свете', 1290.00, 'purevision_hd.jpg', 2, 1, 0, 75);

-- Солнцезащитные очки (category_id = 4)
INSERT INTO products (id, name, description, price, image, category_id, is_new, is_popular, stock) VALUES
(2, 'Солнцезащитные очки "Полярикс"', '100% UV защита, стильный дизайн', 3590.00, 'glasses2.jpg', 4, 0, 1, 10),
(9, 'Солнцезащитные очки "UV Shield"', 'Солнцезащитные очки "UV Shield" с поляризационными линзами обеспечивают 100% защиту от UVA и UVB лучей. Идеальны для вождения автомобиля и активного отдыха - поляризация устраняет блики от воды, снега и дорожного покрытия. Линзы категории 3 затемнения подходят для яркого солнечного света. Ударопрочная оправа из TR-90 пластика и противоударные линзы соответствуют стандартам безопасности ANSI. В комплекте - жесткий футляр и микрофибра.', 3990.00, 'sunglasses1.jpg', 4, 1, 1, 20),
(10, 'Классические солнцезащитные очки "Aviator Classic"', 'Классические солнцезащитные очки "Aviator Classic" в стиле ретро. Двойная мостовая конструкция и каплевидные линзы - легендарный дизайн, вдохновленный авиаторами 1930-х годов. Линзы с градиентным затемнением (сверху темнее, снизу светлее) обеспечивают комфортное ношение за рулем и в городе. Металлическая оправа из нержавеющей стали с покрытием PVD устойчива к царапинам и коррозии. Степень защиты - UV400.', 5490.00, 'sunglasses_aviator.jpg', 4, 0, 1, 12),
(11, 'Модные солнцезащитные очки "Urban Style"', 'Модные солнцезащитные очки "Urban Style" для стильных и уверенных в себе. Крупная геометрическая оправа цвета "черепаховый" из экологичного ацетата создает эффектный образ. Зеркальное покрытие линз не только выглядит эффектно, но и эффективно отражает солнечные лучи. Линзы категории 2 затемнения подходят для городских условий и переменной облачности. Совместимы с диоптриями - можно установить корректирующие линзы у оптика.', 7990.00, 'sunglasses_urban.jpg', 4, 1, 0, 7);

-- ============================================
-- 10. ДОБАВЛЯЕМ АДМИНИСТРАТОРА
-- ============================================
-- Пароль: admin123 (хеширован)
INSERT INTO users (id, email, password, full_name, phone, role, is_guest) VALUES
(1, 'galaevasof06@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Галаева Софья Денисовна', '+79858520484', 'admin', 0);

-- ============================================
-- 11. ТЕСТОВЫЕ ЗАКАЗЫ (для демонстрации)
-- ============================================
INSERT INTO orders (user_id, order_number, total, status, payment_method, delivery_address, created_at) VALUES
(1, 'ORD-20260615-001', 14990.00, 'delivered', 'card', 'г. Москва, ул. Тестовая, д.1, кв.5', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'ORD-20260615-002', 6990.00, 'shipped', 'cash', 'г. Санкт-Петербург, Невский пр., д.100, кв.12', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'ORD-20260615-003', 1290.00, 'new', 'card', 'г. Казань, ул. Баумана, д.10', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Товары в заказах
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 6, 1, 14990.00),
(2, 7, 1, 6990.00),
(3, 3, 1, 1290.00);

-- ============================================
-- 12. ПРОВЕРКА ВСЕХ ТАБЛИЦ
-- ============================================
SELECT '=== ВСЕ ТАБЛИЦЫ В БАЗЕ ДАННЫХ ===' as '';
SHOW TABLES;

SELECT '=== КАТЕГОРИИ ===' as '';
SELECT * FROM categories ORDER BY id;

SELECT '=== ТОВАРЫ ПО КАТЕГОРИЯМ ===' as '';
SELECT 
    p.id,
    p.name,
    p.price,
    p.stock,
    c.name as category_name,
    CASE WHEN p.is_new = 1 THEN 'Да' ELSE 'Нет' END as is_new,
    CASE WHEN p.is_popular = 1 THEN 'Да' ELSE 'Нет' END as is_popular
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
ORDER BY c.id, p.id;

SELECT '=== ПОЛЬЗОВАТЕЛИ ===' as '';
SELECT id, email, full_name, phone, role, created_at FROM users;

SELECT '=== ЗАКАЗЫ ===' as '';
SELECT o.id, o.order_number, o.total, o.status, u.full_name, o.created_at 
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC;

SELECT '=== БАЗА ДАННЫХ ГОТОВА К РАБОТЕ! ===' as '';



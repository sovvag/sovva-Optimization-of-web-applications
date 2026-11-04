<?php
// Функции для авторизации и регистрации

function registerUser($pdo, $email, $password, $full_name, $phone) {
    try {
        // Проверка на существование
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Пользователь с таким email уже существует'];
        }
        
        // Сохраняем пароль в открытом виде (для простоты)
        // В реальном проекте используйте password_hash()!
        
        // Вставляем пользователя
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone, is_guest) VALUES (?, ?, ?, ?, 0)");
        $result = $stmt->execute([$email, $password, $full_name, $phone]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            
            // Переносим корзину из сессии в пользователя
            $session_id = session_id();
            $stmt = $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
            $stmt->execute([$user_id, $session_id]);
            
            return ['success' => true, 'user_id' => $user_id, 'user_name' => $full_name];
        }
        
        return ['success' => false, 'error' => 'Ошибка при регистрации'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()];
    }
}

function loginUser($pdo, $email, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Проверяем пароль (простое сравнение, без хеширования)
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Переносим корзину из сессии в пользователя
            $session_id = session_id();
            $stmt = $pdo->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
            $stmt->execute([$user['id'], $session_id]);
            
            return ['success' => true, 'user' => $user];
        }
        
        return ['success' => false, 'error' => 'Неверный email или пароль'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

function logoutUser() {
    session_destroy();
    return true;
}

function createOrder($pdo, $user_id, $cart_items, $total, $delivery_address, $payment_method) {
    try {
        $pdo->beginTransaction();
        
        // Генерируем номер заказа
        $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Создаем заказ
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total, delivery_address, payment_method) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $order_number, $total, $delivery_address, $payment_method]);
        $order_id = $pdo->lastInsertId();
        
        // Добавляем товары в заказ
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Уменьшаем количество на складе
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Очищаем корзину
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        return ['success' => true, 'order_id' => $order_id, 'order_number' => $order_number];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>

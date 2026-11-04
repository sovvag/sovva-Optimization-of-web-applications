<?php
// Функции для работы с корзиной
function getCartCount($pdo, $session_id) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

function getCartItems($pdo, $session_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.session_id = ?
    ");
    $stmt->execute([$session_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addToCart($pdo, $session_id, $product_id, $quantity = 1) {
    $stmt = $pdo->prepare("
        INSERT INTO cart (session_id, product_id, quantity) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE quantity = quantity + ?
    ");
    return $stmt->execute([$session_id, $product_id, $quantity, $quantity]);
}

function removeFromCart($pdo, $session_id, $product_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
    return $stmt->execute([$session_id, $product_id]);
}

function updateCartQuantity($pdo, $session_id, $product_id, $quantity) {
    if ($quantity <= 0) {
        return removeFromCart($pdo, $session_id, $product_id);
    }
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE session_id = ? AND product_id = ?");
    return $stmt->execute([$quantity, $session_id, $product_id]);
}

function clearCart($pdo, $session_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
    return $stmt->execute([$session_id]);
}

function getCartTotal($pdo, $session_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.session_id = ?
        ");
        $stmt->execute([$session_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch(PDOException $e) {
        return 0;
    }
}
?>
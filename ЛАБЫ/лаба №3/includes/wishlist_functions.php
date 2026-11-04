<?php
// Функции для работы с избранным

function addToWishlist($pdo, $session_id, $product_id, $category_name = 'Основной') {
    try {
        $stmt = $pdo->prepare("INSERT INTO wishlist (session_id, product_id, category_name) VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE added_at = CURRENT_TIMESTAMP");
        return $stmt->execute([$session_id, $product_id, $category_name]);
    } catch(PDOException $e) {
        return false;
    }
}

function removeFromWishlist($pdo, $session_id, $product_id, $category_name = null) {
    $sql = "DELETE FROM wishlist WHERE session_id = ? AND product_id = ?";
    $params = [$session_id, $product_id];
    
    if ($category_name) {
        $sql .= " AND category_name = ?";
        $params[] = $category_name;
    }
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function getWishlistItems($pdo, $session_id, $category_name = null) {
    $sql = "SELECT w.*, p.name, p.price, p.image, p.description 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.session_id = ?";
    $params = [$session_id];
    
    if ($category_name) {
        $sql .= " AND w.category_name = ?";
        $params[] = $category_name;
    }
    
    $sql .= " ORDER BY w.added_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getWishlistCategories($pdo, $session_id) {
    $stmt = $pdo->prepare("SELECT DISTINCT category_name FROM wishlist WHERE session_id = ? ORDER BY category_name");
    $stmt->execute([$session_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function moveToCart($pdo, $session_id, $product_id) {
    // Добавляем в корзину
    $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, 1) 
                            ON DUPLICATE KEY UPDATE quantity = quantity + 1");
    $result = $stmt->execute([$session_id, $product_id]);
    
    // Удаляем из избранного
    if ($result) {
        removeFromWishlist($pdo, $session_id, $product_id);
    }
    
    return $result;
}

function updateWishlistCategory($pdo, $session_id, $product_id, $new_category) {
    $stmt = $pdo->prepare("UPDATE wishlist SET category_name = ? WHERE session_id = ? AND product_id = ?");
    return $stmt->execute([$new_category, $session_id, $product_id]);
}
?>
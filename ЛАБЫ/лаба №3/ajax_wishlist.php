<?php
include 'includes/db.php';
include 'includes/wishlist_functions.php';

$session_id = session_id();
$action = $_POST['action'] ?? '';
$product_id = $_POST['product_id'] ?? 0;

header('Content-Type: application/json');

switch($action) {
    case 'add':
        $category = $_POST['category'] ?? 'Основной';
        $result = addToWishlist($pdo, $session_id, $product_id, $category);
        echo json_encode(['success' => $result]);
        break;
        
    case 'remove':
        $result = removeFromWishlist($pdo, $session_id, $product_id);
        echo json_encode(['success' => $result]);
        break;
        
    case 'move_to_cart':
        $result = moveToCart($pdo, $session_id, $product_id);
        echo json_encode(['success' => $result]);
        break;
        
    case 'update_category':
        $category = $_POST['category'] ?? 'Основной';
        $result = updateWishlistCategory($pdo, $session_id, $product_id, $category);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';
include 'includes/auth_functions.php';

session_start();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Логирование для отладки
file_put_contents('debug.log', date('Y-m-d H:i:s') . ' - Action: ' . $action . "\n", FILE_APPEND);
file_put_contents('debug.log', print_r($_POST, true) . "\n", FILE_APPEND);

switch($action) {
    case 'register':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Валидация
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Имя обязательно';
        } elseif (!preg_match('/^[A-Za-zА-Яа-я\s]+$/u', $name)) {
            $errors[] = 'Имя должно содержать только буквы и пробелы';
        }
        
        if (empty($email)) {
            $errors[] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Неверный формат email';
        }
        
        if (empty($phone)) {
            $errors[] = 'Телефон обязателен';
        }
        
        if (empty($password)) {
            $errors[] = 'Пароль обязателен';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
            exit;
        }
        
        $result = registerUser($pdo, $email, $password, $name, $phone);
        echo json_encode($result);
        break;
        
    case 'login':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'Заполните все поля']);
            exit;
        }
        
        $result = loginUser($pdo, $email, $password);
        echo json_encode($result);
        break;
        
    case 'logout':
        logoutUser();
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
}
?>
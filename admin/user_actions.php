<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
}

require_once('../includes/db.php');

$action = $_GET['action'] ?? '';
$userId = $_GET['id'] ?? null;

switch ($action) {
    case 'create':
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $password]);
        header('Location: users.php');
        break;

    case 'update_profile':
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $tier = $_POST['tier'];
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, tier = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $tier, $userId]);
        header("Location: user_edit.php?id={$userId}");
        break;

    case 'update_balance':
        $type = $_POST['type'];
        $amount = floatval($_POST['amount']);
        $operator = ($type === 'credit') ? '+' : '-';
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance {$operator} ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        header("Location: user_edit.php?id={$userId}");
        break;

    case 'toggle_status':
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentStatus = $stmt->fetchColumn();
        $newStatus = ($currentStatus === 'active') ? 'suspended' : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);
        header("Location: user_edit.php?id={$userId}");
        break;

    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        header('Location: users.php');
        break;

    case 'generate_api_key':
        $apiKey = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?");
        $stmt->execute([$apiKey, $userId]);
        header("Location: user_edit.php?id={$userId}");
        break;

    case 'toggle_api':
        $stmt = $pdo->prepare("UPDATE users SET api_enabled = NOT api_enabled WHERE id = ?");
        $stmt->execute([$userId]);
        header("Location: user_edit.php?id={$userId}");
        break;

    case 'change_password':
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        if ($password !== $confirm_password) {
            header("Location: user_edit.php?id={$userId}&error=password_mismatch");
            exit();
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$password_hash, $userId]);
        header("Location: user_edit.php?id={$userId}&success=password_changed");
        break;
}
?>
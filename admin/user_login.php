<?php
require_once('../includes/session_config.php');
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}
require_once('../includes/db.php');

$userId = $_GET['id'] ?? null;
if ($userId) {
    // Store admin session
    $_SESSION['admin_id'] = $_SESSION['admin'];
    unset($_SESSION['admin']);

    // Set user session
    $_SESSION['user_id'] = $userId;

    header('Location: ../index.php');
    exit();
} else {
    header('Location: users.php');
    exit();
}
?>
<?php
require_once('../includes/session_config.php');
if (isset($_SESSION['admin_id'])) {
    // Restore admin session
    $_SESSION['user_id'] = $_SESSION['admin_id'];
    unset($_SESSION['admin_id']);

    header('Location: dashboard.php');
    exit();
} else {
    header('Location: ../login.php');
    exit();
}
?>

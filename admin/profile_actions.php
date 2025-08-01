<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            header('Location: profile.php?error=password_mismatch');
            exit();
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $password_hash, $_SESSION['admin_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $_SESSION['admin_id']]);
    }

    header('Location: profile.php?success=profile_updated');
    exit();
}
?>

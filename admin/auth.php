<?php
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: index.php?error=1');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin'] = $email;

            $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);

            header('Location: dashboard.php');
            exit();
        } else {
            header('Location: index.php?error=1');
            exit();
        }
    } catch (PDOException $e) {
        die('Login failed: ' . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit();
}
?>
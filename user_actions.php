<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'update_bank_details' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_details = $_POST['bank_details'] ?? '';

    if (!empty($bank_details)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET bank_details = ? WHERE id = ?");
            $stmt->execute([$bank_details, $user_id]);

            // Send email notification to admin
            require_once 'includes/send_email.php';
            $stmt = $pdo->query("SELECT email FROM admins LIMIT 1");
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($admin) {
                $subject = "User Bank Details Updated";
                $body = "<p>User with ID $user_id has updated their bank details.</p>";
                send_email($admin['email'], $subject, $body);
            }

            header('Location: bonus_wallet.php?success=bank_details_updated');
            exit();
        } catch (PDOException $e) {
            header('Location: bonus_wallet.php?error=db_error');
            exit();
        }
    } else {
        header('Location: bonus_wallet.php?error=missing_fields');
        exit();
    }
} else {
    header('Location: bonus_wallet.php');
    exit();
}
?>

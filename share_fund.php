<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sender_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if (!$recipient_email || !$amount || $amount <= 0) {
        header('Location: index.php?error=invalid_input');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Get recipient ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$recipient_email]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            throw new Exception('Recipient not found.');
        }
        $recipient_id = $recipient['id'];

        // Check sender's balance
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$sender_id]);
        $sender = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sender['wallet_balance'] < $amount) {
            throw new Exception('Insufficient balance.');
        }

        // Create fund share request
        $stmt = $pdo->prepare("INSERT INTO fund_shares (sender_id, recipient_id, amount, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$sender_id, $recipient_id, $amount]);

        // Send email notification to admin
        require_once 'includes/send_email.php';
        $stmt = $pdo->query("SELECT email FROM admins LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $subject = "New Fund Share Request";
            $body = "<p>User with ID $sender_id has requested to share $amount with user with ID $recipient_id.</p>";
            send_email($admin['email'], $subject, $body);
        }

        $pdo->commit();
        header('Location: index.php?success=share_requested');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: index.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>

<?php
require_once('includes/session_config.php');
require_once('includes/db.php');
require_once('includes/notifications.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'request') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $bank_details = filter_input(INPUT_POST, 'bank_details', FILTER_SANITIZE_STRING);

    if (!$amount || $amount <= 0 || empty(trim($bank_details))) {
        header('Location: index.php?error=invalid_input');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Check if user has sufficient wallet balance
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['wallet_balance'] < $amount) {
            throw new Exception('Insufficient wallet balance.');
        }

        // Deduct from wallet balance
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        // Create withdrawal request for admin approval
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, bank_details, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $amount, $bank_details]);

        // Send notification for withdrawal request
        notify_withdrawal($user_id, $amount, 'requested');

        $pdo->commit();
        header('Location: index.php?success=withdrawal_requested');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Withdrawal request failed: " . $e->getMessage());
        header('Location: index.php?error=db_error');
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'wallet') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if (!$amount || $amount <= 0) {
        header('Location: index.php?error=invalid_amount');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Check if user has sufficient bonus balance
        $stmt = $pdo->prepare("SELECT bonus_balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['bonus_balance'] < $amount) {
            throw new Exception('Insufficient bonus balance.');
        }

        // Deduct from bonus balance and add to main wallet balance
        $stmt = $pdo->prepare("UPDATE users SET bonus_balance = bonus_balance - ?, wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->execute([$amount, $amount, $user_id]);

        // Create a transaction record
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, 'bonus_to_wallet', ?, 'Completed', 'Bonus to wallet transfer')");
        $stmt->execute([$user_id, $amount]);

        // Send transaction notification
        notify_transaction($user_id, 'bonus_to_wallet', $amount, 'completed', 'Bonus to wallet transfer');

        $pdo->commit();
        header('Location: index.php?success=withdrawal_successful');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Bonus to wallet transfer failed: " . $e->getMessage());
        header('Location: index.php?error=db_error');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>

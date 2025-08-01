<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if (!$amount || $amount <= 0) {
        header('Location: bonus_wallet.php?error=invalid_amount');
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

        // Deduct from bonus balance
        $stmt = $pdo->prepare("UPDATE users SET bonus_balance = bonus_balance - ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        if ($action === 'request') {
            $bank_details = filter_input(INPUT_POST, 'bank_details', FILTER_SANITIZE_STRING);
            if (empty(trim($bank_details))) {
                throw new Exception('Bank details are required.');
            }

            // Create withdrawal request for admin approval
            $stmt = $pdo->prepare("INSERT INTO bonus_withdrawals (user_id, amount, bank_details, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $amount, $bank_details]);

            // Save bank details for future use
            $stmt = $pdo->prepare("UPDATE users SET bank_details = ? WHERE id = ?");
            $stmt->execute([$bank_details, $user_id]);

            $success_message = 'withdrawal_requested';

        } elseif ($action === 'wallet') {
            // Add to main wallet balance
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([$amount, $user_id]);

            // Create a transaction record
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, 'bonus_to_wallet', ?, 'Completed', 'Bonus to wallet transfer')");
            $stmt->execute([$user_id, $amount]);

            $success_message = 'withdrawal_successful';
        }

        // Get updated bonus balance
        $stmt = $pdo->prepare("SELECT bonus_balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();
        header("Location: bonus_wallet.php?success=$success_message&bonus_balance=" . $updated_user['bonus_balance']);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Bonus withdrawal failed: " . $e->getMessage());
        header('Location: bonus_wallet.php?error=db_error');
        exit();
    }
} else {
    header('Location: bonus_wallet.php');
    exit();
}
?>
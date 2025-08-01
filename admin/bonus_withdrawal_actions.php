<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bonus_withdrawals.php');
    exit();
}

$action = $_POST['action'] ?? '';
$withdrawal_id = $_POST['withdrawal_id'] ?? null;

if (!$withdrawal_id || !in_array($action, ['approve', 'reject', 'request'])) {
    header('Location: bonus_withdrawals.php?error=invalid_action');
    exit();
}

if ($action === 'request') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $bank_details = filter_input(INPUT_POST, 'bank_details', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];

    if (!$amount || $amount <= 0 || empty(trim($bank_details))) {
        header('Location: ../bonus_wallet.php?error=invalid_input');
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

        // Deduct from bonus balance and create withdrawal request
        $stmt = $pdo->prepare("UPDATE users SET bonus_balance = bonus_balance - ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        $stmt = $pdo->prepare("INSERT INTO bonus_withdrawals (user_id, amount, bank_details, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $amount, $bank_details]);

        // Save bank details for future use
        $stmt = $pdo->prepare("UPDATE users SET bank_details = ? WHERE id = ?");
        $stmt->execute([$bank_details, $user_id]);

        $pdo->commit();
        header('Location: ../bonus_wallet.php?success=withdrawal_requested');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Bonus withdrawal request failed: " . $e->getMessage());
        header('Location: ../bonus_wallet.php?error=db_error');
        exit();
    }
} else {
    $new_status = $action === 'approve' ? 'approved' : 'rejected';

    try {
        $pdo->beginTransaction();

        // Fetch withdrawal details
        $stmt = $pdo->prepare("SELECT * FROM bonus_withdrawals WHERE id = ?");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$withdrawal) {
            throw new Exception('Withdrawal not found.');
        }

        if ($withdrawal['status'] !== 'pending') {
            throw new Exception('Withdrawal has already been processed.');
        }

        // Update withdrawal status
        $stmt = $pdo->prepare("UPDATE bonus_withdrawals SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $withdrawal_id]);

        // If rejected, refund the user's bonus balance
        if ($new_status === 'rejected') {
            $stmt = $pdo->prepare("UPDATE users SET bonus_balance = bonus_balance + ? WHERE id = ?");
            $stmt->execute([$withdrawal['amount'], $withdrawal['user_id']]);
        }

        $pdo->commit();
        header('Location: bonus_withdrawals.php?success=action_completed');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Bonus withdrawal action failed: " . $e->getMessage());
        header('Location: bonus_withdrawals.php?error=db_error');
        exit();
    }
}
?>
<?php
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('auth_check.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['withdrawal_id'])) {
    $action = $_POST['action'];
    $withdrawal_id = $_POST['withdrawal_id'];

    try {
        $pdo->beginTransaction();

        // Get withdrawal details
        $stmt = $pdo->prepare("SELECT * FROM bonus_withdrawals WHERE id = ?");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($withdrawal) {
            $user_id = $withdrawal['user_id'];
            $amount = $withdrawal['amount'];

            if ($action === 'approve') {
                // Just update the status to approved
                $stmt = $pdo->prepare("UPDATE bonus_withdrawals SET status = 'approved' WHERE id = ?");
                $stmt->execute([$withdrawal_id]);
            } elseif ($action === 'reject') {
                // Update the status to rejected and refund the user's bonus wallet
                $stmt = $pdo->prepare("UPDATE bonus_withdrawals SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$withdrawal_id]);

                $stmt = $pdo->prepare("UPDATE users SET bonus_balance = bonus_balance + ? WHERE id = ?");
                $stmt->execute([$amount, $user_id]);
            } elseif ($action === 'cancel') {
                // Update the status to cancelled, and the funds are not returned
                $stmt = $pdo->prepare("UPDATE bonus_withdrawals SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$withdrawal_id]);
            }
        }

        $pdo->commit();
        header('Location: bank_withdrawals.php');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        // Handle error
        header('Location: bank_withdrawals.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: bank_withdrawals.php');
    exit();
}
?>

<?php
require_once('auth_check.php');
require_once('../includes/db.php');

$action = $_GET['action'] ?? '';
$transactionId = $_GET['id'] ?? null;

if ($action === 'cancel' && $transactionId) {
    try {
        $pdo->beginTransaction();

        // Fetch transaction details
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'Processing'");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            // Mark transaction as cancelled
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'Cancelled' WHERE id = ?");
            $stmt->execute([$transactionId]);
        }

        $pdo->commit();
        header('Location: transactions.php');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
} elseif ($action === 'fail' && $transactionId) {
    try {
        $pdo->beginTransaction();

        // Fetch transaction details
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'Completed'");
        $stmt->execute([$transactionId]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            // Refund the user
            $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([abs($transaction['amount']), $transaction['user_id']]);

            // Mark transaction as failed
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'Failed' WHERE id = ?");
            $stmt->execute([$transactionId]);
        }

        $pdo->commit();
        header('Location: transactions.php');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
}

header('Location: transactions.php');
exit();
?>

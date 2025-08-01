<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: fund_shares.php');
    exit();
}

$action = $_POST['action'] ?? '';
$share_id = $_POST['share_id'] ?? null;

if (!$share_id || !in_array($action, ['approve', 'reject', 'cancel'])) {
    header('Location: fund_shares.php?error=invalid_action');
    exit();
}

try {
    $pdo->beginTransaction();

    // Fetch share details
    $stmt = $pdo->prepare("SELECT * FROM fund_shares WHERE id = ?");
    $stmt->execute([$share_id]);
    $share = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$share) {
        throw new Exception('Share request not found.');
    }

    if ($share['status'] !== 'pending') {
        throw new Exception('Share request has already been processed.');
    }

    // Update share status
    $stmt = $pdo->prepare("UPDATE fund_shares SET status = ? WHERE id = ?");
    $stmt->execute([$action, $share_id]);

    if ($action === 'approve') {
        // Deduct from sender's wallet
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->execute([$share['amount'], $share['sender_id']]);

        // Credit recipient's wallet
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->execute([$share['amount'], $share['recipient_id']]);

    } elseif ($action === 'reject') {
        // Refund sender's wallet
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->execute([$share['amount'], $share['sender_id']]);
    }
    // For 'cancel', do nothing with the funds

    $pdo->commit();
    header('Location: fund_shares.php?success=action_completed');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Fund share action failed: " . $e->getMessage());
    header('Location: fund_shares.php?error=db_error');
    exit();
}
?>
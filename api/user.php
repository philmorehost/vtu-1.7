<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}
$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT name, wallet_balance, bonus_balance, email, phone, tier, referral_link FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (empty($user['referral_link'])) {
            // Generate and save the referral link if it doesn't exist
            $referralLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/register.php?ref=" . $userId;
            $updateStmt = $pdo->prepare("UPDATE users SET referral_link = ? WHERE id = ?");
            $updateStmt->execute([$referralLink, $userId]);
            $user['referral_link'] = $referralLink;
        }
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

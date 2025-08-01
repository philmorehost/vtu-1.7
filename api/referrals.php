<?php
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch referred users and the bonus earned from each
    $stmt = $pdo->prepare("
        SELECT u.name AS referred_user, u.created_at AS join_date, COALESCE(SUM(t.amount), 0) AS bonus_earned
        FROM users u
        LEFT JOIN transactions t ON u.id = t.user_id AND t.type = 'referral_bonus' AND t.user_id = ?
        WHERE u.referred_by = ?
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total bonus earned
    $total_bonus = array_sum(array_column($referrals, 'bonus_earned'));

    echo json_encode([
        'referrals' => $referrals,
        'total_bonus' => $total_bonus
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's referrals and bonus earned
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.created_at, COALESCE(SUM(t.amount), 0) AS bonus_earned
    FROM users u
    LEFT JOIN transactions t ON u.id = t.user_id AND t.type = 'referral_bonus'
    WHERE u.referred_by = ?
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute([$user_id]);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Referral History</h1>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Users You've Referred</h2>
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">User</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Email</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date Joined</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Bonus Earned</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($referrals as $referral): ?>
                        <tr>
                            <td class="text-left py-3 px-4"><?= htmlspecialchars($referral['name']) ?></td>
                            <td class="text-left py-3 px-4"><?= htmlspecialchars($referral['email']) ?></td>
                            <td class="text-left py-3 px-4"><?= htmlspecialchars($referral['created_at']) ?></td>
                            <td class="text-left py-3 px-4 text-green-500">₦<?= htmlspecialchars(number_format($referral['bonus_earned'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
header('Content-Type: application/json');
require_once('includes/db.php');
require_once('includes/notifications.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit();
}

try {
    // Get user by email
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    if ($user['is_verified']) {
        echo json_encode(['success' => false, 'message' => 'Email is already verified']);
        exit();
    }

    // Delete existing verification tokens for this user
    $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Create new verification token
    $verification_token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, token, expires) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $verification_token, $expires]);

    // Send verification email
    if (send_verification_email($user['id'], $verification_token)) {
        echo json_encode(['success' => true, 'message' => 'Verification email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send verification email']);
    }

} catch (PDOException $e) {
    error_log("Resend verification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>
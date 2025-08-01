<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 0;
    $payment_proof = $_POST['payment_proof'] ?? '';
    $bank_id = $_POST['bank_id'] ?? 0;

    if ($amount > 0 && $user_id > 0 && !empty($payment_proof) && $bank_id > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO payment_orders (user_id, amount, payment_proof, bank_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $amount, $payment_proof, $bank_id]);

            // Send email notification to admin
            require_once 'includes/send_email.php';
            $stmt = $pdo->query("SELECT email FROM admins LIMIT 1");
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($admin) {
                $subject = "New Payment Order";
                $body = "<p>User with ID $user_id has placed a new payment order for the amount of $amount.</p>";
                send_email($admin['email'], $subject, $body);
            }

            header('Location: index.php?success=order_placed');
            exit();

        } catch (PDOException $e) {
            die('Order placement failed: ' . $e->getMessage());
        }
    } else {
        header('Location: index.php?error=order_failed');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>

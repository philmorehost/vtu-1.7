<?php
require_once('includes/session_config.php');
require_once('includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_proof = filter_input(INPUT_POST, 'payment_proof', FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];

    if (!$amount || $amount <= 0 || empty(trim($payment_proof)) || !isset($_POST['bank_id'])) {
        // Handle invalid input
        header('Location: index.php?error=invalid_input');
        exit();
    }

    $bank_id = filter_input(INPUT_POST, 'bank_id', FILTER_VALIDATE_INT);

    if (!$bank_id) {
        header('Location: index.php?error=invalid_bank');
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payment_orders (user_id, bank_id, amount, payment_proof, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $bank_id, $amount, $payment_proof]);

        header('Location: index.php?success=payment_submitted');
        exit();
    } catch (PDOException $e) {
        error_log("Payment order creation failed: " . $e->getMessage());
        header('Location: index.php?error=db_error');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}

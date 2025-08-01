<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }
}

require_once('../includes/db.php');

$action = $_GET['action'] ?? '';
$bankId = $_GET['id'] ?? null;

switch ($action) {
    case 'add':
        $bank_name = $_POST['bank_name'];
        $account_name = $_POST['account_name'];
        $account_number = $_POST['account_number'];
        $charge = $_POST['charge'];
        $instructions = $_POST['instructions'];

        $stmt = $pdo->prepare("INSERT INTO bank_details (bank_name, account_name, account_number, charge, instructions) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$bank_name, $account_name, $account_number, $charge, $instructions]);

        header('Location: bank_settings.php');
        break;

    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM bank_details WHERE id = ?");
        $stmt->execute([$bankId]);

        header('Location: bank_settings.php');
        break;
}
?>

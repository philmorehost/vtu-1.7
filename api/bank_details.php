<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

try {
    $stmt = $pdo->query("SELECT id, bank_name, account_name, account_number, charge, instructions FROM bank_details");
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $details]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

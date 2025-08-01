<?php
require_once('config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For API calls, we should return a JSON error
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please contact support.',
        'error_code' => 'DB_CONN_FAIL'
    ]);
    // Log the detailed error for the admin
    error_log("Database Connection Error: " . $e->getMessage());
    exit();
}
?>

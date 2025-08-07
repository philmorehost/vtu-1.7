<?php
require_once('config.php');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If we are in an API context (which we can assume if this file is included by an API endpoint),
    // we should return a JSON error, not just die.
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    // Log the detailed error for the admin, but don't show it to the user.
    error_log("Database Connection Error: " . $e->getMessage());
    // Provide a generic error to the client.
    echo json_encode([
        'success' => false,
        'message' => 'A critical error occurred with the database connection. Please contact support.'
    ]);
    exit();
}
?>

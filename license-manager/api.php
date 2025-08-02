<?php
header('Content-Type: application/json');

// In a real application, you would connect to your database here.
// require_once('db.php');

$response = ['status' => 0]; // Default to invalid

if (isset($_POST['key']) && isset($_POST['domain'])) {
    $license_key = $_POST['key'];
    $domain = $_POST['domain'];

    // --- Placeholder validation logic ---
    // In a real application, you would query your database to validate the key and domain.
    // For now, we'll consider any key that starts with "VALID-" to be valid.
    if (strpos($license_key, 'VALID-') === 0) {
        // Here you would also check if the domain is linked to the key in your database.
        $response['status'] = 1; // Valid
    }
}

echo json_encode($response);
?>

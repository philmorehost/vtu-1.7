<?php
// Paystack webhook handler

// Get the POST data.
$event = file_get_contents('php://input');

// For security, you should verify the webhook signature.
// This is a simplified example.
$paystack_secret_key = 'YOUR_PAYSTACK_SECRET_KEY'; // Replace with your secret key
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'];

if (hash_hmac('sha512', $event, $paystack_secret_key) !== $signature) {
    // Invalid signature
    http_response_code(401);
    exit();
}

$event_data = json_decode($event, true);

// Log the event for debugging
file_put_contents('webhook.log', $event . "\n", FILE_APPEND);

if ($event_data['event'] === 'charge.success') {
    $customer_email = $event_data['data']['customer']['email'];
    $domain = $event_data['data']['metadata']['domain'] ?? 'N/A';

    // Generate a new license key
    $new_license_key = 'VALID-' . strtoupper(bin2hex(random_bytes(8)));

    // --- Placeholder for database interaction ---
    // In a real application, you would save the license key, email, and domain to your database.
    $license_db_file = 'licenses.db.txt';
    $db_entry = "{$new_license_key},{$customer_email},{$domain}\n";
    file_put_contents($license_db_file, $db_entry, FILE_APPEND);
    // -----------------------------------------

    // --- Placeholder for sending email ---
    // In a real application, you would use a proper email library to send the license key to the customer.
    $email_subject = 'Your New License Key';
    $email_body = "Thank you for your purchase. Your new license key is: {$new_license_key}";
    // mail($customer_email, $email_subject, $email_body);
    // ------------------------------------
}

http_response_code(200);
?>

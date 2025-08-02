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

    // --- Send email notification ---
    require 'includes/PHPMailer/src/Exception.php';
    require 'includes/PHPMailer/src/PHPMailer.php';
    require 'includes/PHPMailer/src/SMTP.php';

    $settings_file = 'settings.json';
    $settings = [];
    if (file_exists($settings_file)) {
        $settings = json_decode(file_get_contents($settings_file), true);
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = $settings['smtp_host'] ?? 'smtp.example.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_user'] ?? 'user@example.com';
        $mail->Password   = $settings['smtp_pass'] ?? 'password';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $settings['smtp_port'] ?? 587;

        //Recipients
        $mail->setFrom($settings['admin_email'] ?? 'from@example.com', $settings['site_name'] ?? 'License Manager');
        $mail->addAddress($customer_email);
        $mail->addBCC($settings['admin_email'] ?? 'admin@example.com');

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Your New License Key';
        $mail->Body    = "Thank you for your purchase. Your new license key is: <b>{$new_license_key}</b>";
        $mail->AltBody = "Thank you for your purchase. Your new license key is: {$new_license_key}";

        $mail->send();
    } catch (Exception $e) {
        // Log email error
        file_put_contents('email.log', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n", FILE_APPEND);
    }
    // ------------------------------------
}

http_response_code(200);
?>

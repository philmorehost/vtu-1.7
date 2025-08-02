<?php
require_once('db.php');
$license_key = 'N/A';
if (isset($_GET['ref'])) {
    // In a real app, you would fetch the license key from the database based on the transaction reference.
    // For this example, we'll just show a placeholder.
    // A more secure way would be to pass a session token or a one-time code.
    $stmt = $pdo->prepare("SELECT license_key FROM licenses WHERE transaction_ref = ?");
    // $stmt->execute([$_GET['ref']]);
    // $license = $stmt->fetch();
    // if ($license) {
    //     $license_key = $license['license_key'];
    // }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { background: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 100%; max-width: 36rem; text-align: center; }
        h1 { color: #16a34a; font-size: 2.25rem; margin-bottom: 1rem; }
        p { color: #6b7280; margin-bottom: 2rem; }
        .license-key { background: #f3f4f6; border: 2px dashed #d1d5db; padding: 1rem; border-radius: 0.5rem; font-size: 1.25rem; font-weight: 600; margin-bottom: 2rem; word-wrap: break-word; }
        .btn { background-color: #3b82f6; color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Successful!</h1>
        <p>Thank you for your purchase. Your license key has been sent to your email. You can also copy it from below.</p>
        <div class="license-key" id="licenseKey">
            <!-- This would be dynamically populated -->
            VALID-LICENSE-KEY-EXAMPLE
        </div>
        <button class="btn" onclick="copyLicense()">Copy Key</button>
    </div>
    <script>
        function copyLicense() {
            const licenseKey = document.getElementById('licenseKey').innerText;
            navigator.clipboard.writeText(licenseKey).then(() => {
                alert('License key copied to clipboard!');
            }, (err) => {
                alert('Failed to copy license key.');
            });
        }
    </script>
</body>
</html>

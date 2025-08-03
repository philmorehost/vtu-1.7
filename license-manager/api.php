<?php
header('Content-Type: application/json');

require_once('db.php');

$response = ['status' => 0, 'message' => 'Invalid license key or domain.'];

if (isset($_POST['key']) && isset($_POST['domain'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM licenses WHERE license_key = ? AND domain = ? AND status = 'active'");
        $stmt->execute([$_POST['key'], $_POST['domain']]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($license) {
            $response['status'] = 1;
            $response['message'] = 'License is valid.';
        }
    } catch (PDOException $e) {
        // In a real app, log this error.
        $response['message'] = 'A database error occurred.';
    }
} else {
    $response['message'] = 'Missing key or domain parameter.';
}

echo json_encode($response);
?>

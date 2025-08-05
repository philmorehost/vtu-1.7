<?php
require_once('includes/db.php');

echo "<h1>Setting up test provider</h1>";

try {
    // Insert provider
    $stmt = $pdo->prepare("
        INSERT INTO api_providers (provider_module, name, status, api_key, base_url)
        VALUES ('NaijaResultPinsProvider', 'NaijaResultPins', 'active', 'test_api_key', 'https://www.naijaresultpins.com/api/v1')
        ON DUPLICATE KEY UPDATE name = 'NaijaResultPins', status = 'active', api_key = 'test_api_key', base_url = 'https://www.naijaresultpins.com/api/v1'
    ");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT id FROM api_providers WHERE provider_module = 'NaijaResultPinsProvider'");
    $stmt->execute();
    $providerId = $stmt->fetchColumn();
    echo "<p>Provider inserted/updated with ID: {$providerId}</p>";

    if ($providerId) {
        // Insert route
        $stmt = $pdo->prepare("
            INSERT INTO api_provider_routes (api_provider_id, service_type, status)
            VALUES (?, 'exam', 'active')
            ON DUPLICATE KEY UPDATE status = 'active'
        ");
        $stmt->execute([$providerId]);
        echo "<p>Route inserted/updated.</p>";
    }


    echo "<p>Test provider setup complete.</p>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

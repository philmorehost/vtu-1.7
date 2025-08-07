<?php
function detectNetworkFromDB(string $phoneNumber, PDO $pdo): ?array
{
    if (empty($phoneNumber)) {
        return null;
    }

    // Network prefixes for Nigerian networks
    $networkPrefixes = [
        'MTN' => ['0803', '0806', '0703', '0706', '0813', '0810', '0814', '0816', '0903', '0906', '0913', '0916', '0702', '0704'],
        'GLO' => ['0805', '0807', '0705', '0815', '0811', '0905', '0915'],
        'AIRTEL' => ['0802', '0808', '0708', '0812', '0701', '0902', '0907', '0901', '0904', '0912'],
        '9MOBILE' => ['0809', '0818', '0817', '0909', '0908']
    ];

    $detectedNetworkName = null;
    $phonePrefix = substr($phoneNumber, 0, 4);

    foreach ($networkPrefixes as $network => $prefixes) {
        if (in_array($phonePrefix, $prefixes)) {
            $detectedNetworkName = $network;
            break;
        }
    }

    if ($detectedNetworkName) {
        try {
            // Get network details from the database
            $stmt = $pdo->prepare("SELECT * FROM networks WHERE name = ? AND status = 'active'");
            $stmt->execute([$detectedNetworkName]);
            $networkData = $stmt->fetch(PDO::FETCH_ASSOC);
            return $networkData ?: null;
        } catch (PDOException $e) {
            // Log error if needed
            error_log("Failed to query network details: " . $e->getMessage());
            return null;
        }
    }

    return null;
}

<?php
/**
 * Reusable helper functions
 */

/**
 * Detects the mobile network based on a phone number prefix.
 *
 * @param string $phoneNumber The phone number to check.
 * @param PDO $pdo The database connection object.
 * @return array|null The network data from the database, or null if not found.
 */
function detectNetworkByPhone(string $phoneNumber, PDO $pdo): ?array {
    if (empty($phoneNumber)) {
        return null;
    }

    // Fetch prefixes from the database
    $networkPrefixes = [];
    try {
        $stmt = $pdo->query("SELECT n.name, np.prefix FROM networks n JOIN network_prefixes np ON n.id = np.network_id WHERE n.status = 'active'");
        $prefixesFromDb = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        if ($prefixesFromDb) {
            foreach($prefixesFromDb as $networkName => $prefixes) {
                $networkPrefixes[$networkName] = array_column($prefixes, 'prefix');
            }
        }
    } catch (PDOException $e) {
        // Fallback to hardcoded prefixes if db query fails
        error_log("Error fetching network prefixes from DB: " . $e->getMessage());
    }

    if (empty($networkPrefixes)) {
        $networkPrefixes = [
            'MTN' => ['07025', '07026', '0803', '0806', '0703', '0704', '0706', '0707', '0813', '0810', '0814', '0816', '0903', '0906', '0913', '0916'],
            'GLO' => ['0805', '0807', '0705', '0815', '0811', '0905', '0915'],
            'AIRTEL' => ['0802', '0808', '0708', '0812', '0701', '0902', '0907', '0901', '0904', '0911', '0912'],
            '9MOBILE' => ['0809', '0818', '0817', '0909', '0908']
        ];
    }

    $detectedNetworkName = null;
    $phonePrefix = substr($phoneNumber, 0, 4);

    foreach ($networkPrefixes as $network => $prefixes) {
        if (in_array($phonePrefix, $prefixes)) {
            $detectedNetworkName = $network;
            break;
        }
    }

    if ($detectedNetworkName) {
        $stmt = $pdo->prepare("SELECT * FROM networks WHERE name = ? AND status = 'active'");
        $stmt->execute([$detectedNetworkName]);
        $networkData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $networkData ?: null;
    }

    return null;
}

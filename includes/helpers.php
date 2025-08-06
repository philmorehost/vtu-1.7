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
    $stmt = $pdo->query("SELECT n.name, np.prefix FROM networks n JOIN network_prefixes np ON n.id = np.network_id WHERE n.status = 'active'");
    $prefixesFromDb = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

    $networkPrefixes = [];
    foreach($prefixesFromDb as $networkName => $prefixes) {
        $networkPrefixes[$networkName] = array_column($prefixes, 'prefix');
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

<?php
require_once 'includes/db.php';

$sql = "
CREATE TABLE `network_prefixes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `network_id` int(11) NOT NULL,
  `prefix` varchar(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `network_id` (`network_id`),
  CONSTRAINT `network_prefixes_ibfk_1` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$pdo->exec($sql);

$prefixes = [
    'MTN' => ['07025', '07026', '0803', '0806', '0703', '0704', '0706', '0707', '0813', '0810', '0814', '0816', '0903', '0906', '0913', '0916'],
    'GLO' => ['0805', '0807', '0705', '0815', '0811', '0905', '0915'],
    'Airtel' => ['0802', '0808', '0708', '0812', '0701', '0902', '0907', '0901', '0904', '0911', '0912'],
    '9mobile' => ['0809', '0818', '0817', '0909', '0908']
];

foreach ($prefixes as $networkName => $networkPrefixes) {
    $stmt = $pdo->prepare("SELECT id FROM networks WHERE name = ?");
    $stmt->execute([$networkName]);
    $network = $stmt->fetch();
    if ($network) {
        $networkId = $network['id'];
        foreach ($networkPrefixes as $prefix) {
            $stmt = $pdo->prepare("INSERT INTO network_prefixes (network_id, prefix) VALUES (?, ?)");
            $stmt->execute([$networkId, $prefix]);
        }
    }
}

echo "Migration to create and populate network_prefixes table has been executed successfully.\n";
?>

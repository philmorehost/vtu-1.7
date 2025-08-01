<?php
require_once('../includes/db.php');

$stmt = $pdo->query("SELECT site_logo FROM site_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($settings && !empty($settings['site_logo'])) {
    echo json_encode(['success' => true, 'logo_path' => $settings['site_logo']]);
} else {
    echo json_encode(['success' => false]);
}
?>

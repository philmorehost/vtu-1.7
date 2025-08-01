<?php
// admin/includes/session_config.php
require_once(__DIR__ . '/db.php');

$stmt = $pdo->query("SELECT session_timeout, cache_control FROM site_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($settings) {
    // Set cache control header (this can be done even after session starts)
    header("Cache-Control: " . $settings['cache_control']);
    
    // Only configure session settings if session hasn't started yet
    if (session_status() === PHP_SESSION_NONE) {
        // Set session timeout
        ini_set('session.gc_maxlifetime', $settings['session_timeout'] * 60);
        session_set_cookie_params($settings['session_timeout'] * 60);
        session_start();
    }
} else {
    // Fallback if settings not found
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
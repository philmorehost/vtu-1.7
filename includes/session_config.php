<?php
// includes/session_config.php
require_once(__DIR__ . '/db.php');

// Default settings in case the database query fails or columns are missing
$session_timeout = 30; // Default to 30 minutes
$cache_control = 'no-cache, must-revalidate'; // Default cache control

try {
    $stmt = $pdo->query("SELECT session_timeout, cache_control FROM site_settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($settings) {
        // Use database values only if they are set and not empty
        $session_timeout = !empty($settings['session_timeout']) ? (int)$settings['session_timeout'] : $session_timeout;
        $cache_control = !empty($settings['cache_control']) ? $settings['cache_control'] : $cache_control;
    }
} catch (PDOException $e) {
    // Log the error, but don't break the page. Defaults will be used.
    error_log("Could not fetch site settings: " . $e->getMessage());
}

// Set cache control header
if (!headers_sent()) {
    header("Cache-Control: " . $cache_control);
}

// Only configure session settings if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    // Set session timeout
    ini_set('session.gc_maxlifetime', $session_timeout * 60);
    session_set_cookie_params($session_timeout * 60);
    session_start();
}
?>

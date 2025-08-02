<?php
// --- Obfuscated License Check ---
// INSTRUCTIONS FOR USE:
// 1. Rename this file to something inconspicuous (e.g., 'system_health.php', 'init.php').
// 2. Include this file at the top of your protected PHP scripts (e.g., `require_once('system_health.php');`).
// 3. Call the check function, e.g., `_0x2a1b_c4d3e5();`

// --- Configuration ---
// Replace with your actual license key and the URL to your license manager API.
$_0x4a3b_d5e2c1 = 'YOUR_LICENSE_KEY'; // Obfuscated variable for license key
$_0x5b2c_e4d3f2 = 'https://your-license-manager.com/api.php'; // Obfuscated variable for API URL

// --- State Management ---
// This file will store the last check date. It should be writable by the script.
$_0x6c1d_f3e2a1 = __DIR__ . '/.status_cache'; // Obfuscated variable for cache file

// The main check function.
function _0x2a1b_c4d3e5() {
    global $_0x4a3b_d5e2c1, $_0x5b2c_e4d3f2, $_0x6c1d_f3e2a1;

    $last_check_date = 0;
    if (file_exists($_0x6c1d_f3e2a1)) {
        $last_check_date = (int)file_get_contents($_0x6c1d_f3e2a1);
    }

    // Check if it's time for a daily check
    if (time() > $last_check_date + (24 * 60 * 60)) {
        $is_valid = _0x3b2c_d4e3f1();
        if ($is_valid) {
            file_put_contents($_0x6c1d_f3e2a1, time());
        } else {
            // Handle grace period (e.g., allow for 3 days of failed checks)
            $grace_period_end = $last_check_date + (3 * 24 * 60 * 60);
            if (time() > $grace_period_end) {
                _0x1c3d_e2f1a0(); // Shutdown
            }
        }
    }
}

// The function that communicates with the license server.
function _0x3b2c_d4e3f1() {
    global $_0x4a3b_d5e2c1, $_0x5b2c_e4d3f2;

    $domain = $_SERVER['SERVER_NAME'];
    $data = ['key' => $_0x4a3b_d5e2c1, 'domain' => $domain];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true,
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($_0x5b2c_e4d3f2, false, $context);

    if ($result === FALSE) { return false; }

    $response = json_decode($result, true);
    return isset($response['status']) && $response['status'] == 1;
}

// The shutdown function.
function _0x1c3d_e2f1a0() {
    // This will include the "buy license" page and stop all further script execution.
    require_once(__DIR__ . '/buy_license.html');
    exit();
}
?>

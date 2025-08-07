<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

// Function to handle standard image uploads to the database
function handle_db_image_upload($file_key, $column_name, $pdo) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                die("Failed to create directory: " . $upload_dir);
            }
        }
        $file_name = uniqid() . '-' . basename($_FILES[$file_key]['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_file)) {
            $image_path = 'assets/images/' . $file_name;
            $stmt = $pdo->prepare("UPDATE site_settings SET $column_name = ? WHERE id = 1");
            $stmt->execute([$image_path]);
        }
    }
}

// Function to handle the favicon upload to a JSON file
function handle_favicon_upload() {
    $settings_file = '../includes/extra_settings.json';
    $settings = json_decode(file_get_contents($settings_file), true);

    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = 'favicon-' . uniqid() . '.' . pathinfo($_FILES['site_favicon']['name'], PATHINFO_EXTENSION);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['site_favicon']['tmp_name'], $target_file)) {
            // Store a path relative to the web root
            $settings['site_favicon'] = 'assets/images/' . $file_name;
            file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT));
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle standard image uploads
    handle_db_image_upload('site_logo', 'site_logo', $pdo);
    handle_db_image_upload('auth_image', 'auth_image', $pdo);

    // Handle favicon upload to JSON file
    handle_favicon_upload();

    // Update other settings in the database
    $site_name = $_POST['site_name'];
    $session_timeout = $_POST['session_timeout'];
    $cache_control = $_POST['cache_control'];
    $referral_bonus_tier1 = $_POST['referral_bonus_tier1'];
    $referral_bonus_tier2 = $_POST['referral_bonus_tier2'];
    $admin_email = $_POST['admin_email'];
    $smtp_host = $_POST['smtp_host'];
    $smtp_port = $_POST['smtp_port'];
    $smtp_user = $_POST['smtp_user'];
    $smtp_pass = $_POST['smtp_pass'];
    $smtp_from_email = $_POST['smtp_from_email'];
    $smtp_from_name = $_POST['smtp_from_name'];

    $stmt = $pdo->prepare("UPDATE site_settings SET site_name = ?, session_timeout = ?, cache_control = ?, referral_bonus_tier1 = ?, referral_bonus_tier2 = ?, admin_email = ?, smtp_host = ?, smtp_port = ?, smtp_user = ?, smtp_pass = ?, smtp_from_email = ?, smtp_from_name = ? WHERE id = 1");
    $stmt->execute([$site_name, $session_timeout, $cache_control, $referral_bonus_tier1, $referral_bonus_tier2, $admin_email, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_from_email, $smtp_from_name]);

    header('Location: site_settings.php');
    exit();
}
?>

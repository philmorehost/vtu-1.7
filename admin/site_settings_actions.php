<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Function to handle image uploads and return the new path
    function handle_image_upload($file_key) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    // Consider a more graceful error handling
                    error_log("Failed to create directory: " . $upload_dir);
                    return null;
                }
            }
            $file_name = uniqid() . '-' . basename($_FILES[$file_key]['name']);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_file)) {
                return 'assets/images/' . $file_name;
            }
        }
        return null;
    }

    // Prepare data for the SQL query
    $params = [
        'site_name' => $_POST['site_name'],
        'session_timeout' => $_POST['session_timeout'],
        'cache_control' => $_POST['cache_control'],
        'referral_bonus_tier1' => $_POST['referral_bonus_tier1'],
        'referral_bonus_tier2' => $_POST['referral_bonus_tier2'],
        'admin_email' => $_POST['admin_email'],
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => $_POST['smtp_port'],
        'smtp_user' => $_POST['smtp_user'],
        'smtp_pass' => $_POST['smtp_pass'],
        'smtp_from_email' => $_POST['smtp_from_email'],
        'smtp_from_name' => $_POST['smtp_from_name']
    ];

    // Handle file uploads
    $site_logo_path = handle_image_upload('site_logo');
    $auth_image_path = handle_image_upload('auth_image');

    // Add image paths to params if they were uploaded
    if ($site_logo_path) {
        $params['site_logo'] = $site_logo_path;
    }
    if ($auth_image_path) {
        $params['auth_image'] = $auth_image_path;
    }

    // Build the SQL query dynamically
    $sql_parts = [];
    foreach (array_keys($params) as $key) {
        $sql_parts[] = "$key = ?";
    }
    $sql = "UPDATE site_settings SET " . implode(', ', $sql_parts) . " WHERE id = 1";

    // Execute the single update query
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($params));

    header('Location: site_settings.php');
    exit();
}
?>

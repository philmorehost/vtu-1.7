<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = $_POST['site_name'];
    $session_timeout = $_POST['session_timeout'];
    $cache_control = $_POST['cache_control'];

    function handle_image_upload($file_key, $column_name, $pdo) {
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

    handle_image_upload('site_logo', 'site_logo', $pdo);
    handle_image_upload('auth_image', 'auth_image', $pdo);

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

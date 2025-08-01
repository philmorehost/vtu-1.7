<?php
if (session_status() === PHP_SESSION_NONE) {
    // Session should be started by session_config.php
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
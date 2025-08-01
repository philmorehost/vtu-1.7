<?php
// admin/apimanager/index.php

// Start the session to check authentication
session_start();

// --- Authentication Check ---
// Replace 'is_admin_logged_in' with your actual session check logic.
// Example: Check if a specific admin ID is set in the session
$isAuthenticated = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);

// Example: Check if a specific boolean flag is set in the session
// $isAuthenticated = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// --- Redirect if NOT authenticated ---
if (!$isAuthenticated) {
    // Replace 'login.php' with the correct path to your admin login page
    // Use an absolute path relative to your domain root for clarity and to avoid issues
    header("Location: /admin/index.php"); // Adjust '/admin/login.php' as needed
    exit(); // Crucial: Stop script execution after redirect
}

// --- If authenticated, show the API Manager index page ---
$title = 'API Manager';
// Include your standard admin header
require_once '../includes/header.php'; // Adjust path if needed based on your structure
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($title); ?></h2>
    <p>Select an API module to manage:</p>
    <ul class="mt-4 space-y-2">
        <li><a href="airtime.php" class="text-blue-500 hover:underline">Airtime API</a></li>
        <li><a href="data.php" class="text-blue-500 hover:underline">Data API</a></li>
        <li><a href="bulk_sms.php" class="text-blue-500 hover:underline">Bulk SMS API</a></li>
        <li><a href="cabletv.php" class="text-blue-500 hover:underline">Cable TV API</a></li>
        <li><a href="electric.php" class="text-blue-500 hover:underline">Electricity API</a></li>
        <li><a href="exam_pin.php" class="text-blue-500 hover:underline">Exam Pin API</a></li>
        <li><a href="recharge_card.php" class="text-blue-500 hover:underline">Recharge Card API</a></li>
        <li><a href="betting.php" class="text-blue-500 hover:underline">Betting API</a></li>
        <li><a href="gift_card.php" class="text-blue-500 hover:underline">Gift Card API</a></li>
        <!-- Add links for other API modules as needed -->
    </ul>
</div>

<?php
// Include your standard admin footer
require_once '../includes/footer.php'; // Adjust path if needed based on your structure
?>
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <!-- New Modular System -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-magic mr-2"></i>New Modular System
            </h3>
            <p class="text-sm text-blue-600 mb-3">Simplified API management with pre-integrated providers</p>
            <ul class="space-y-2">
                <li><a href="modular_manager.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-cogs mr-2"></i>Modular API Manager
                </a></li>
                <li><a href="api_gateway_manager.php" class="text-blue-500 hover:underline">
                    <i class="fas fa-route mr-2"></i>Legacy Gateway Manager
                </a></li>
            </ul>
            <div class="mt-3 text-xs text-blue-500">
                ✨ No more JSON mappings needed!
            </div>
        </div>

        <!-- Legacy Individual APIs -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">
                <i class="fas fa-list mr-2"></i>Legacy Individual APIs
            </h3>
            <p class="text-sm text-gray-600 mb-3">Original individual API management (deprecated)</p>
            <ul class="space-y-1 text-sm">
                <li><a href="airtime.php" class="text-gray-600 hover:underline">Airtime API</a></li>
                <li><a href="data.php" class="text-gray-600 hover:underline">Data API</a></li>
                <li><a href="bulk_sms.php" class="text-gray-600 hover:underline">Bulk SMS API</a></li>
                <li><a href="cabletv.php" class="text-gray-600 hover:underline">Cable TV API</a></li>
                <li><a href="electric.php" class="text-gray-600 hover:underline">Electricity API</a></li>
                <li><a href="exam_pin.php" class="text-gray-600 hover:underline">Exam Pin API</a></li>
                <li><a href="recharge_card.php" class="text-gray-600 hover:underline">Recharge Card API</a></li>
                <li><a href="betting.php" class="text-gray-600 hover:underline">Betting API</a></li>
                <li><a href="gift_card.php" class="text-gray-600 hover:underline">Gift Card API</a></li>
            </ul>
        </div>
    </div>
</div>

<?php
// Include your standard admin footer
require_once '../includes/footer.php'; // Adjust path if needed based on your structure
?>
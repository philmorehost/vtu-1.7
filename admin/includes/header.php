<?php
// admin/includes/header_new.php - Modern Admin Header
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once(__DIR__ . '/../../includes/session_config.php');
require_once(__DIR__ . '/../auth_check.php');
require_once(__DIR__ . '/../../includes/db.php');

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" x-data="{sidebarCollapsed: false}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Panel') ?> - <?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Defensive JavaScript utilities to prevent common errors -->
    <script src="../assets/js/defensive-utils.js"></script>
    <style>
        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        /* Sidebar styles for transition */
        #sidebar {
            transition: width 0.3s ease-in-out, padding 0.3s ease-in-out;
        }
        /* Navigation hover effects */
        .nav-item {
            position: relative;
            overflow: hidden;
        }
        .nav-item:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        .nav-item:hover:before {
            left: 100%;
        }
        /* Active navigation item */
        .nav-active {
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            border-left: 4px solid #60A5FA;
        }
        /* Logo animation */
        .logo-container {
            transition: transform 0.3s ease;
        }
        .logo-container:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50" x-data>
    <div class="flex h-screen bg-gray-100">
        <!-- Modern Sidebar -->
        <div
            id="sidebar"
            :class="sidebarCollapsed ? 'w-20' : 'w-64'"
            class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white shadow-2xl fixed md:relative h-screen z-20 md:block hidden overflow-y-auto transition-all duration-300"
            x-data="{sidebarCollapsed: false}"
            x-bind:class="sidebarCollapsed ? 'w-20' : 'w-64'"
        >
            <div class="flex flex-col h-full">
                <!-- Logo Section -->
                <div class="flex justify-center items-center p-6 border-b border-gray-700 logo-container">
                    <?php if (!empty($settings['site_logo'])): ?>
                        <img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Site Logo" class="h-12 w-auto" :class="sidebarCollapsed ? 'mx-auto' : ''">
                    <?php else: ?>
                        <div class="text-center" x-show="!sidebarCollapsed">
                            <h2 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                                <?= htmlspecialchars($settings['site_name'] ?? 'VTU Platform') ?>
                            </h2>
                            <p class="text-sm text-gray-400 mt-1">Admin Panel</p>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Navigation Menu -->
                <nav class="flex-1 px-2 py-6 space-y-2">
                    <a href="/admin/dashboard.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-0 md:mr-3 text-blue-400 group-hover:text-blue-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Dashboard</span>
                    </a>
                    <a href="/admin/users.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-users w-5 h-5 mr-0 md:mr-3 text-green-400 group-hover:text-green-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Users</span>
                    </a>
                    <a href="/admin/transactions.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-exchange-alt w-5 h-5 mr-0 md:mr-3 text-yellow-400 group-hover:text-yellow-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Transactions</span>
                    </a>
                    <a href="/admin/payment_orders.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-credit-card w-5 h-5 mr-0 md:mr-3 text-purple-400 group-hover:text-purple-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Payment Orders</span>
                    </a>
                    <a href="/admin/fund_shares.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-share-alt w-5 h-5 mr-0 md:mr-3 text-cyan-400 group-hover:text-cyan-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Fund Shares</span>
                    </a>
                    <a href="/admin/bonus_withdrawals.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-gift w-5 h-5 mr-0 md:mr-3 text-pink-400 group-hover:text-pink-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Bonus Withdrawals</span>
                    </a>
                    <a href="/admin/withdrawals.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-money-bill-wave w-5 h-5 mr-0 md:mr-3 text-red-400 group-hover:text-red-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Withdrawals</span>
                    </a>
                    <a href="/admin/notifications.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-bell w-5 h-5 mr-0 md:mr-3 text-orange-400 group-hover:text-orange-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Notifications</span>
                    </a>
                    <!-- API Menu with Submenu -->
                    <div class="group">
                        <button type="button" class="w-full flex justify-between items-center py-2.5 px-2 rounded transition duration-200 hover:bg-gray-700 text-left focus:outline-none">
                            <span class="flex items-center">
                                <i class="fas fa-code mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">API SETTINGS</span>
                            </span>
                            <i class="fas fa-chevron-down" x-show="!sidebarCollapsed"></i>
                        </button>
                        <div class="hidden group-hover:block ml-0 md:ml-8">
                            <a href="/admin/services.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-chart-line w-4 h-4 mr-0 md:mr-2 text-gray-400"></i>
                                <span x-show="!sidebarCollapsed">Service Prices</span>
                            </a>
                            <a href="/admin/apimanager/airtime.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-mobile-alt mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Airtime</span>
                            </a>
                            <a href="/admin/apimanager/data.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-wifi mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Data</span>
                            </a>
                            <a href="/admin/apimanager/bulk_sms.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-envelope mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Bulk SMS</span>
                            </a>
                            <a href="/admin/apimanager/cabletv.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-tv mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Cable TV</span>
                            </a>
                            <a href="/admin/apimanager/electric.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-bolt mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Electricity</span>
                            </a>
                            <a href="/admin/apimanager/exam_pin.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-key mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Exam Pin</span>
                            </a>
                            <a href="/admin/apimanager/recharge_card.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-credit-card mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Recharge Card</span>
                            </a>
                            <a href="/admin/apimanager/betting.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-futbol mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Betting</span>
                            </a>
                            <a href="/admin/apimanager/gift_card.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-gift mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Gift Card</span>
                            </a>
                            <a href="/admin/apimanager/api_gateway_manager.php" class="flex items-center py-2 px-2 rounded-md transition duration-200 hover:bg-gray-600 text-sm">
                                <i class="fas fa-code w-4 h-4 mr-0 md:mr-2 text-gray-400"></i>
                                <span x-show="!sidebarCollapsed">API Manager</span>
                            </a>
                        </div>
                    </div>
                    <!-- Security Menu with Submenu -->
                    <div class="group">
                        <button type="button" class="w-full flex justify-between items-center py-2.5 px-2 rounded transition duration-200 hover:bg-gray-700 text-left focus:outline-none">
                            <span class="flex items-center">
                                <i class="fas fa-shield-alt mr-0 md:mr-2 text-blue-400"></i>
                                <span x-show="!sidebarCollapsed">Security</span>
                            </span>
                            <i class="fas fa-chevron-down" x-show="!sidebarCollapsed"></i>
                        </button>
                        <div class="hidden group-hover:block ml-0 md:ml-8">
                            <a href="/admin/transaction_limits.php" class="flex items-center py-2 px-2 rounded transition duration-200 hover:bg-gray-600">
                                <i class="fas fa-sliders-h mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Transaction Limits</span>
                            </a>
                            <a href="/admin/blocked_identifiers.php" class="flex items-center py-2 px-2 rounded transition duration-200 hover:bg-gray-600">
                                <i class="fas fa-ban mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Blocked Identifiers</span>
                            </a>
                            <a href="/admin/sms_sender_moderation.php" class="flex items-center py-2 px-2 rounded transition duration-200 hover:bg-gray-600">
                                <i class="fas fa-id-card-alt mr-0 md:mr-2"></i>
                                <span x-show="!sidebarCollapsed">Manage SMS SenderID</span>
                            </a>
                        </div>
                    </div>
                    <a href="/admin/bank_settings.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-university w-5 h-5 mr-0 md:mr-3 text-teal-400 group-hover:text-teal-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Bank Settings</span>
                    </a>
                    <a href="/admin/site_settings.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-cog w-5 h-5 mr-0 md:mr-3 text-gray-400 group-hover:text-gray-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Site Settings</span>
                    </a>
                    <a href="/admin/chat.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-comments w-5 h-5 mr-0 md:mr-3 text-blue-400 group-hover:text-blue-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Chat</span>
                    </a>
                    <a href="/admin/profile.php" class="nav-item flex items-center py-3 px-2 rounded-lg transition-all duration-200 hover:bg-gray-700 group">
                        <i class="fas fa-user-cog w-5 h-5 mr-0 md:mr-3 text-purple-400 group-hover:text-purple-300"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Profile</span>
                    </a>
                </nav>
                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-gray-700">
                    <!-- Collapse/Expand Button (desktop only) -->
                    <button
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="w-full flex items-center justify-center py-3 px-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition-all duration-200 group"
                    >
                        <i :class="sidebarCollapsed ? 'fas fa-angles-right' : 'fas fa-angles-left'" class="w-5 h-5 mr-2 transition-transform duration-200"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Collapse</span>
                        <span class="font-medium" x-show="sidebarCollapsed">Exp</span>
                    </button>
                    <a href="/admin/logout.php" class="w-full flex items-center justify-center py-3 px-2 mt-2 bg-red-600 hover:bg-red-700 rounded-lg transition-all duration-200 group">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-2"></i>
                        <span class="font-medium" x-show="!sidebarCollapsed">Logout</span>
                    </a>
                </div>
            </div>
        </div>
                <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Modern Top Bar -->
            <div class="flex items-center justify-between p-4 bg-white shadow-lg border-b border-gray-200">
                <div class="flex items-center">
                    <!-- Mobile Toggle -->
                    <button id="sidebar-toggle" class="md:hidden p-2 rounded-md bg-gray-800 text-white mr-3 hover:bg-gray-700 transition-colors">
                        <i class="fas fa-bars h-5 w-5"></i>
                    </button>
                    
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?= htmlspecialchars($title ?? 'Admin Panel') ?></h1>
                        <p class="text-sm text-gray-500 hidden md:block">Manage your platform efficiently</p>
                    </div>
                </div>
                
                <!-- Top Bar Actions -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="current-time"></span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700">Admin</span>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content Area -->
            <div class="flex-1 p-4 md:p-6 overflow-y-auto bg-gray-50">
                <!-- Page content will be injected here -->

    <script>
        // Current time display
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }
        
        // Update time every second
        setInterval(updateTime, 1000);
        updateTime();

        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarToggleFooter = document.getElementById('sidebar-toggle-footer');
            const toggleIcon = document.getElementById('toggle-icon-footer');
            const toggleText = document.getElementById('toggle-text-footer');

            function toggleSidebar() {
                sidebar.classList.toggle('sidebar-collapsed');
                
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    toggleIcon.className = 'fas fa-angles-right w-5 h-5 mr-2 transition-transform duration-200';
                    toggleText.textContent = 'Expand';
                } else {
                    toggleIcon.className = 'fas fa-angles-left w-5 h-5 mr-2 transition-transform duration-200';
                    toggleText.textContent = 'Collapse';
                }
            }

            // Mobile toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                });
            }

            // Desktop collapse/expand
            if (sidebarToggleFooter) {
                sidebarToggleFooter.addEventListener('click', toggleSidebar);
            }
        });
    </script>

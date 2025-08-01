<?php
require_once('includes/session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vending Platform</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen flex flex-col">

    <?php if (isset($_SESSION['admin_id'])): ?>
        <div class="bg-yellow-400 text-black text-center p-2">
            You are logged in as a user. <a href="admin/switch_back.php" class="font-bold underline">Switch back to admin</a>.
        </div>
    <?php endif; ?>

    <!-- Main Application Container -->
    <div id="app-container" class="flex-grow flex flex-col items-center justify-center">

        <!-- Dashboard Page -->
        <div id="dashboard-page" class="page flex-grow p-4 pb-20 md:p-8 max-w-screen-md mx-auto w-full">
            <!-- Content from previous dashboard HTML -->
            <!-- Top Header Section -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-2xl font-bold text-gray-800">
                    Welcome back, <span id="customer-name">John Doe</span>
                </div>
                <button id="refresh-button" class="p-2 rounded-full bg-white shadow-md text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>

            <!-- Wallet and Bonus Balance Box -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white p-6 rounded-2xl shadow-lg mb-6 relative overflow-hidden">
                <div class="flex justify-between items-start mb-4 flex-wrap">
                    <div class="w-full mb-4">
                        <div class="flex items-center">
                            <p class="text-sm opacity-80">Wallet Balance</p>
                            <button id="toggle-balance-visibility" class="text-sm opacity-80 hover:opacity-100 transition-opacity flex items-center ml-2">
                                <i class="fas fa-eye mr-1"></i> <span id="toggle-text">Hide</span>
                            </button>
                        </div>
                        <p id="wallet-balance" class="text-3xl font-bold">₦ <span class="balance-value">0.00</span></p>
                    </div>
                    <div class="w-full">
                        <p class="text-sm opacity-80">Bonus Balance</p>
                        <p id="bonus-balance" class="text-xl font-semibold">₦ <span class="balance-value">0.00</span></p>
                    </div>
                </div>

                <div class="absolute bottom-0 right-0 p-4 flex space-x-2">
                    <button id="submit-payment-button" class="bg-green-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-xl transform hover:scale-105 transition-transform" title="Submit Payment">
                        <i class="fas fa-paper-plane text-xl"></i>
                    </button>
                    <button id="withdraw-button" class="bg-yellow-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-xl transform hover:scale-105 transition-transform" title="Withdraw">
                        <i class="fas fa-wallet text-xl"></i>
                    </button>
                    <button id="dashboard-fund-wallet-button" class="bg-white text-blue-700 w-12 h-12 rounded-full flex items-center justify-center shadow-xl transform hover:scale-105 transition-transform" title="Fund Wallet">
                        <i class="fas fa-plus text-xl"></i>
                    </button>
                    <button id="share-fund-button" class="bg-purple-500 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-xl transform hover:scale-105 transition-transform" title="Share Fund">
                        <i class="fas fa-share-alt text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Virtual Accounts Scrolling Section -->
            <div class="mb-6">

                <div class="flex overflow-x-auto pb-4 space-x-4 scroll-container">
                    <!-- Virtual Account Card 1 -->
                    <div class="flex-shrink-0 w-64 bg-white p-4 rounded-xl shadow-md border border-gray-200">
                        <p class="font-semibold text-gray-800 mb-1">John Doe Monnify</p>
                        <p class="font-semibold text-gray-800 mb-1">Wema Bank</p>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-bold text-blue-600 text-lg" id="account-num-1">9876543210</p>
                            </div>
                            <button class="copy-account-btn p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors" data-account="9876543210">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-600">Fee: ₦50.00 per transaction</p>
                    </div>

                    <!-- Virtual Account Card 2 -->
                    <div class="flex-shrink-0 w-64 bg-white p-4 rounded-xl shadow-md border border-gray-200">
                        <p class="font-semibold text-gray-800 mb-1">John Doe B-Wave</p>
                        <p class="font-semibold text-gray-800 mb-1">Zenith Bank</p>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-bold text-blue-600 text-lg" id="account-num-2">0123456789</p>
                            </div>
                            <button class="copy-account-btn p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors" data-account="0123456789">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-600">Fee: ₦45.00 per transaction</p>
                    </div>

                    <!-- Virtual Account Card 3 (Example) -->
                    <div class="flex-shrink-0 w-64 bg-white p-4 rounded-xl shadow-md border border-gray-200">
                        <p class="font-semibold text-gray-800 mb-1">John Doe Paystack</p>
                        <p class="font-semibold text-gray-800 mb-1">GTBank</p>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-bold text-blue-600 text-lg" id="account-num-3">1122334455</p>
                            </div>
                            <button class="copy-account-btn p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors" data-account="1122334455">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-600">Fee: ₦60.00 per transaction</p>
                    </div>

                    <!-- New Virtual Account Card 4 -->
                    <div class="flex-shrink-0 w-64 bg-white p-4 rounded-xl shadow-md border border-gray-200">
                        <p class="font-semibold text-gray-800 mb-1">John Doe Flutterwave</p>
                        <p class="font-semibold text-gray-800 mb-1">Access Bank</p>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-bold text-blue-600 text-lg" id="account-num-4">9988776655</p>
                            </div>
                            <button class="copy-account-btn p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors" data-account="9988776655">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-600">Fee: ₦55.00 per transaction</p>
                    </div>

                    <!-- New Virtual Account Card 5 -->
                    <div class="flex-shrink-0 w-64 bg-white p-4 rounded-xl shadow-md border border-gray-200">
                        <p class="font-semibold text-gray-800 mb-1">John Doe Providus</p>
                        <p class="font-semibold text-gray-800 mb-1">Providus Bank</p>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <p class="font-bold text-blue-600 text-lg" id="account-num-5">1234567890</p>
                            </div>
                            <button class="copy-account-btn p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors" data-account="1234567890">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-600">Fee: ₦40.00 per transaction</p>
                    </div>
                </div>
            </div>

            <!-- Service Buttons Section -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <button class="service-btn bg-white p-4 rounded-xl shadow-md flex flex-col items-center justify-center text-gray-700 hover:bg-blue-50 transition-colors" data-service="data">
                    <i class="fas fa-wifi text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium">Data</span>
                </button>
                <button class="service-btn bg-white p-4 rounded-xl shadow-md flex flex-col items-center justify-center text-gray-700 hover:bg-blue-50 transition-colors" data-service="airtime">
                    <i class="fas fa-mobile-alt text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium">Airtime</span>
                </button>
                <button class="service-btn bg-white p-4 rounded-xl shadow-md flex flex-col items-center justify-center text-gray-700 hover:bg-blue-50 transition-colors" data-service="electricity">
                    <i class="fas fa-bolt text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium">Electricity</span>
                </button>
                <button class="service-btn bg-white p-4 rounded-xl shadow-md flex flex-col items-center justify-center text-gray-700 hover:bg-blue-50 transition-colors" data-service="cabletv">
                    <i class="fas fa-tv text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium">Cable TV</span>
                </button>
                <button class="service-btn bg-white p-4 rounded-xl shadow-md flex flex-col items-center justify-center text-gray-700 hover:bg-blue-50 transition-colors" data-service="betting">
                    <i class="fas fa-dice text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium">Betting</span>
                </button>
                <button id="more-services-btn" class="service-btn bg-white p-4 rounded-xl shadow-md flex flex-col items-center justify-center text-gray-700 hover:bg-blue-50 transition-colors col-span-1" data-service="more">
                    <i class="fas fa-ellipsis-h text-2xl text-blue-600 mb-2"></i>
                    <span class="text-sm font-medium">More</span>
                </button>
            </div>

            <!-- Get Rewarded for Inviting Users Box -->
            <div class="bg-gradient-to-r from-green-500 to-green-700 text-white p-6 rounded-2xl shadow-lg mb-6 flex flex-col items-center text-center">
                <i class="fas fa-gift text-4xl mb-3"></i>
                <h2 class="text-xl font-bold mb-2">Get Rewarded for Inviting Users!</h2>
                <p class="text-sm opacity-90 mb-4">Share your unique referral link and earn bonuses for every friend who joins.</p>
                <div class="flex items-center space-x-2">
                    <input type="text" id="referral-link-display" class="flex-grow p-2 rounded-lg border border-gray-300 bg-gray-50 text-gray-700" value="" readonly>
                    <button id="copy-referral-link-button" class="p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="flex space-x-4 mt-4">
                    <button id="view-referrals-button" class="bg-white text-green-700 px-6 py-3 rounded-full font-semibold shadow-md hover:bg-green-100 transition-colors">
                        View Referrals
                    </button>
                </div>
            </div>

            <!-- Recent Transactions Section -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold text-gray-700">Recent Transactions</h2>
                    <button id="view-all-transactions-btn" class="text-blue-600 text-sm font-medium hover:underline">View All</button>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md" id="recent-transactions">
                    <!-- Recent transactions will be loaded here dynamically -->
                </div>
            </div>

            <!-- New: Transaction Calculator Button on Dashboard -->
            <div class="mb-6">
                <button id="transaction-calculator-dashboard-btn" class="w-full bg-purple-600 text-white p-4 rounded-xl font-semibold shadow-md hover:bg-purple-700 transition-colors flex items-center justify-center">
                    <i class="fas fa-calculator text-2xl mr-3"></i> Transaction Calculator
                </button>
            </div>

        </div>

        <!-- Fund Wallet Page -->
        <div id="fund-wallet-page" class="page p-4 md:p-8 max-w-screen-md mx-auto w-full">
            <div class="flex justify-between items-center mb-6">
                <button id="back-to-dashboard-from-fund" class="p-2 rounded-full bg-white shadow-md text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="text-2xl font-bold text-gray-800 text-center flex-grow">Fund Wallet</h2>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Choose Payment Method</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- ATM Card Payment -->
                    <button class="payment-method-btn bg-blue-50 p-4 rounded-xl shadow-sm flex items-center justify-between hover:bg-blue-100 transition-colors" data-gateway="atm">
                        <div class="flex items-center">
                            <i class="fas fa-credit-card text-blue-600 text-2xl mr-3"></i>
                            <span class="font-medium text-gray-800">ATM Card</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>

                    <!-- Monnify -->
                    <button class="payment-method-btn bg-blue-50 p-4 rounded-xl shadow-sm flex items-center justify-between hover:bg-blue-100 transition-colors" data-gateway="monnify">
                        <div class="flex items-center">
                            <img src="https://placehold.co/32x32/000000/FFFFFF?text=M" alt="Monnify Logo" class="mr-3 rounded-full">
                            <span class="font-medium text-gray-800">Monnify</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>

                    <!-- Flutterwave -->
                    <button class="payment-method-btn bg-blue-50 p-4 rounded-xl shadow-sm flex items-center justify-between hover:bg-blue-100 transition-colors" data-gateway="flutterwave">
                        <div class="flex items-center">
                            <img src="https://placehold.co/32x32/000000/FFFFFF?text=F" alt="Flutterwave Logo" class="mr-3 rounded-full">
                            <span class="font-medium text-gray-800">Flutterwave</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>

                    <!-- Paystack -->
                    <button class="payment-method-btn bg-blue-50 p-4 rounded-xl shadow-sm flex items-center justify-between hover:bg-blue-100 transition-colors" data-gateway="paystack">
                        <div class="flex items-center">
                            <img src="https://placehold.co/32x32/000000/FFFFFF?text=P" alt="Paystack Logo" class="mr-3 rounded-full">
                            <span class="font-medium text-gray-800">Paystack</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>

                    <!-- Merchant B-wave -->
                    <button class="payment-method-btn bg-blue-50 p-4 rounded-xl shadow-sm flex items-center justify-between hover:bg-blue-100 transition-colors" data-gateway="bwave">
                        <div class="flex items-center">
                            <img src="https://placehold.co/32x32/000000/FFFFFF?text=B" alt="B-Wave Logo" class="mr-3 rounded-full">
                            <span class="font-medium text-gray-800">Merchant B-wave</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>

                    <!-- Payvessel -->
                    <button class="payment-method-btn bg-blue-50 p-4 rounded-xl shadow-sm flex items-center justify-between hover:bg-blue-100 transition-colors" data-gateway="payvessel">
                        <div class="flex items-center">
                            <img src="https://placehold.co/32x32/000000/FFFFFF?text=V" alt="Payvessel Logo" class="mr-3 rounded-full">
                            <span class="font-medium text-gray-800">Payvessel</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Generic Service Page -->
        <div id="service-page" class="page p-4 md:p-8 max-w-screen-md mx-auto w-full">
            <div class="flex justify-between items-center mb-6">
                <button id="back-to-dashboard-from-service" class="p-2 rounded-full bg-white shadow-md text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 id="service-page-title" class="text-2xl font-bold text-gray-800 text-center flex-grow">Service Details</h2>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <p class="text-gray-700 text-lg mb-4">This is the page for <span id="current-service-name" class="font-bold text-blue-600"></span>.</p>

                <!-- Data Vending Form -->
                <div id="data-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Data Top-up</h3>
                    <form id="data-vending-form">
                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-gray-700 text-sm font-medium">Purchase Type</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" id="data-bulk-purchase-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900" id="data-purchase-type-text">Single Purchase</span>
                            </label>
                        </div>

                        <div class="mb-4" id="data-single-phone-input">
                            <label for="data-phone-number" class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
                            <input type="tel" id="data-phone-number" placeholder="e.g., 08012345678" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="11" required>
                        </div>

                        <div class="mb-4 hidden" id="data-bulk-phone-input">
                            <label for="data-phone-numbers-bulk" class="block text-gray-700 text-sm font-medium mb-2">Phone Numbers (one per line)</label>
                            <textarea id="data-phone-numbers-bulk" placeholder="Enter phone numbers, one per line" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 h-32 resize-none"></textarea>
                            <p class="text-right text-xs text-gray-500"><span id="data-recipient-count">0</span> recipients</p>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2">Detected Network</label>
                                <p id="data-detected-network" class="text-lg font-bold text-gray-800">N/A</p>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2 text-gray-600 text-sm">Override Network</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" value="" id="data-network-override-toggle" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-6" id="data-manual-network-selection" style="display: none;">
                            <label for="data-manual-network" class="block text-gray-700 text-sm font-medium mb-2">Select Network</label>
                            <select id="data-manual-network" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Network</option>
                                <option value="MTN">MTN</option>
                                <option value="Glo">Glo</option>
                                <option value="Airtel">Airtel</option>
                                <option value="9mobile">9mobile</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="data-plan" class="block text-gray-700 text-sm font-medium mb-2">Select Data Plan</label>
                            <select id="data-plan" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select a plan</option>
                                <!-- Data plans will be loaded dynamically based on network -->
                            </select>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-gray-700 text-sm font-medium">Schedule Transaction</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" id="data-schedule-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div id="data-schedule-fields" class="mb-6 hidden">
                            <div class="mb-4">
                                <label for="data-schedule-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
                                <input type="date" id="data-schedule-date" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-4">
                                <label for="data-schedule-time" class="block text-gray-700 text-sm font-medium mb-2">Time</label>
                                <input type="time" id="data-schedule-time" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mb-6 hidden" id="data-bulk-total-cost-section">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Total Cost</label>
                            <p id="data-bulk-total-cost" class="text-2xl font-bold text-gray-800">₦0.00</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Buy Data</button>
                    </form>
                </div>

                <!-- Airtime Vending Form -->
                <div id="airtime-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Airtime Top-up</h3>
                    <form id="airtime-vending-form">
                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-gray-700 text-sm font-medium">Purchase Type</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" id="airtime-bulk-purchase-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900" id="airtime-purchase-type-text">Single Purchase</span>
                            </label>
                        </div>

                        <div class="mb-4" id="airtime-single-phone-input">
                            <label for="airtime-phone-number" class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
                            <input type="tel" id="airtime-phone-number" placeholder="e.g., 08012345678" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="11" required>
                        </div>

                        <div class="mb-4 hidden" id="airtime-bulk-phone-input">
                            <label for="airtime-phone-numbers-bulk" class="block text-gray-700 text-sm font-medium mb-2">Phone Numbers (one per line)</label>
                            <textarea id="airtime-phone-numbers-bulk" placeholder="Enter phone numbers, one per line" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 h-32 resize-none"></textarea>
                            <p class="text-right text-xs text-gray-500"><span id="airtime-recipient-count">0</span> recipients</p>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2">Detected Network</label>
                                <p id="airtime-detected-network" class="text-lg font-bold text-gray-800">N/A</p>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2 text-gray-600 text-sm">Override Network</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" value="" id="airtime-network-override-toggle" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4" id="airtime-manual-network-selection" style="display: none;">
                            <label for="airtime-manual-network" class="block text-gray-700 text-sm font-medium mb-2">Select Network</label>
                            <select id="airtime-manual-network" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Network</option>
                                <option value="MTN">MTN</option>
                                <option value="Glo">Glo</option>
                                <option value="Airtel">Airtel</option>
                                <option value="9mobile">9mobile</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="airtime-amount" class="block text-gray-700 text-sm font-medium mb-2">Amount (₦)</label>
                            <input type="number" id="airtime-amount" placeholder="e.g., 1000" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" min="50" required>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-gray-700 text-sm font-medium">Schedule Transaction</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" id="airtime-schedule-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div id="airtime-schedule-fields" class="mb-6 hidden">
                            <div class="mb-4">
                                <label for="airtime-schedule-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
                                <input type="date" id="airtime-schedule-date" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-4">
                                <label for="airtime-schedule-time" class="block text-gray-700 text-sm font-medium mb-2">Time</label>
                                <input type="time" id="airtime-schedule-time" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mb-6 hidden" id="airtime-bulk-total-cost-section">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Total Cost</label>
                            <p id="airtime-bulk-total-cost" class="text-2xl font-bold text-gray-800">₦0.00</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Buy Airtime</button>
                    </form>
                </div>

                <!-- Electricity Vending Form -->
                <div id="electricity-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Electricity Bill Payment</h3>
                    <form id="electricity-vending-form">
                        <div class="mb-4">
                            <label for="electricity-service-type" class="block text-gray-700 text-sm font-medium mb-2">Service Type</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="electricity-type" value="prepaid" id="electricity-prepaid" class="form-radio text-blue-600" checked>
                                    <span class="ml-2 text-gray-700">Prepaid</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="electricity-type" value="postpaid" id="electricity-postpaid" class="form-radio text-blue-600">
                                    <span class="ml-2 text-gray-700">Postpaid</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="meter-number" class="block text-gray-700 text-sm font-medium mb-2">Meter Number</label>
                            <input type="text" id="meter-number" placeholder="Enter meter number" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="disco-provider" class="block text-gray-700 text-sm font-medium mb-2">Electricity Provider (Disco)</label>
                            <select id="disco-provider" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Disco</option>
                                <option value="ikeja_electric">Ikeja Electric (IE)</option>
                                <option value="eko_electric">Eko Electric (EKEDC)</option>
                                <option value="abuja_electric">Abuja Electric (AEDC)</option>
                                <option value="ibadan_electric">Ibadan Electric (IBEDC)</option>
                                <!-- Add more as needed -->
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="electricity-amount" class="block text-gray-700 text-sm font-medium mb-2">Amount (₦)</label>
                            <input type="number" id="electricity-amount" placeholder="e.g., 5000" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" min="1000" required>
                        </div>
                        <div id="electricity-token-section" class="mb-6">
                            <label for="electricity-token" class="block text-gray-700 text-sm font-medium mb-2">Token Number</label>
                            <input type="text" id="electricity-token" placeholder="Generated token will appear here" class="w-full p-3 rounded-lg border border-gray-300 bg-gray-100 focus:outline-none" readonly>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Pay Electricity Bill</button>
                    </form>
                </div>

                <!-- Cable TV Vending Form -->
                <div id="cabletv-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Cable TV Subscription</h3>
                    <form id="cabletv-vending-form">
                        <div class="mb-4">
                            <label for="cabletv-provider" class="block text-gray-700 text-sm font-medium mb-2">Cable TV Provider</label>
                            <select id="cabletv-provider" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Provider</option>
                                <option value="dstv">DSTV</option>
                                <option value="gotv">GOtv</option>
                                <option value="startimes">Startimes</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="smart-card-number" class="block text-gray-700 text-sm font-medium mb-2">Smart Card Number</label>
                            <input type="text" id="smart-card-number" placeholder="Enter smart card number" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <button type="button" id="verify-smart-card-btn" class="w-full bg-blue-500 text-white p-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors mb-4">Verify Smart Card</button>

                        <!-- Verification Result Display -->
                        <div id="cabletv-verification-result" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                            <p class="text-sm text-gray-600 mb-2"><i class="fas fa-check-circle text-green-500 mr-1"></i> Verification Successful!</p>
                            <p class="mb-1"><span class="font-semibold">Customer Name:</span> <span id="verified-customer-name"></span></p>
                            <p class="mb-1"><span class="font-semibold">Current Plan:</span> <span id="verified-subscription-status"></span></p>
                            <div class="mt-3">
                                <label for="cabletv-plan" class="block text-gray-700 text-sm font-medium mb-2">Select Plan/Bouquet</label>
                                <select id="cabletv-plan" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select a plan</option>
                                    <!-- Plans will be loaded dynamically based on provider and verification -->
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="cabletv-buy-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors" disabled>Subscribe Cable TV</button>
                    </form>
                </div>

                <!-- Betting Wallet Funding Form -->
                <div id="betting-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Fund Betting Wallet</h3>
                    <form id="betting-funding-form">
                        <div class="mb-4">
                            <label for="betting-platform" class="block text-gray-700 text-sm font-medium mb-2">Betting Platform</label>
                            <select id="betting-platform" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Platform</option>
                                <option value="bet9ja">Bet9ja</option>
                                <option value="sportybet">SportyBet</option>
                                <option value="nairabet">NairaBet</option>
                                <option value="betking">BetKing</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="betting-user-id" class="block text-gray-700 text-sm font-medium mb-2">User ID / Account Number</label>
                            <input type="text" id="betting-user-id" placeholder="Enter user ID" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="mb-6">
                            <label for="betting-amount" class="block text-gray-700 text-sm font-medium mb-2">Amount (₦)</label>
                            <input type="number" id="betting-amount" placeholder="e.g., 2000" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" min="100" required>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Fund Wallet</button>
                    </form>
                </div>

                <!-- Exam Vending Form -->
                <div id="exam-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Exam Pin Purchase</h3>
                    <form id="exam-vending-form">
                        <div class="mb-4">
                            <label for="exam-type" class="block text-gray-700 text-sm font-medium mb-2">Exam Type</label>
                            <select id="exam-type" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Exam</option>
                                <option value="waec">WAEC</option>
                                <option value="neco">NECO</option>
                                <option value="jamb">JAMB</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="exam-quantity" class="block text-gray-700 text-sm font-medium mb-2">Quantity</label>
                            <input type="number" id="exam-quantity" placeholder="Number of pins" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" value="1" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Total Amount</label>
                            <p id="exam-total-amount" class="text-2xl font-bold text-gray-800">₦0.00</p>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Buy Exam Pin(s)</button>
                    </form>
                </div>

                <!-- Bulk SMS Sending Form -->
                <div id="bulksms-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Send Bulk SMS</h3>
                    <form id="bulksms-sending-form">
                        <div class="mb-4">
                            <label for="sms-sender-id" class="block text-gray-700 text-sm font-medium mb-2">Sender ID</label>
                            <select id="sms-sender-id" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Sender ID</option>
                                <!-- Sender IDs will be loaded dynamically -->
                            </select>
                            <button type="button" id="register-sender-id-btn" class="mt-2 w-full bg-gray-200 text-gray-700 p-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">Register New Sender ID</button>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-gray-700 text-sm font-medium">Recipient Source</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" id="use-phonebook-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Use Phone Book</span>
                            </label>
                        </div>

                        <div class="mb-4" id="manual-recipient-input">
                            <label for="recipient-numbers" class="block text-gray-700 text-sm font-medium mb-2">Recipient Numbers (comma-separated or one per line)</label>
                            <textarea id="recipient-numbers" placeholder="e.g., 08012345678, 07098765432" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 h-24 resize-none" required></textarea>
                            <p class="text-right text-xs text-gray-500"><span id="recipient-count">0</span> recipients</p>
                            <div class="mt-2 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" value="" id="save-contacts-toggle" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900">Save these contacts to Phone Book</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 hidden" id="phonebook-selection-section">
                            <label for="phonebook-group-select" class="block text-gray-700 text-sm font-medium mb-2">Select Contact Group</label>
                            <select id="phonebook-group-select" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a group</option>
                                <!-- Groups will be loaded dynamically -->
                            </select>
                            <button type="button" id="manage-phonebook-btn" class="mt-2 w-full bg-gray-200 text-gray-700 p-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors">Manage Phone Book</button>
                            <div id="selected-phonebook-contacts-display" class="mt-2 text-sm text-gray-600"></div>
                        </div>

                        <div class="mb-4">
                            <label for="sms-message" class="block text-gray-700 text-sm font-medium mb-2">Message</label>
                            <textarea id="sms-message" placeholder="Type your message here..." class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 h-32 resize-none" required></textarea>
                            <p class="text-right text-xs text-gray-500"><span id="char-count">0</span> characters | <span id="sms-units">0</span> SMS units</p>
                        </div>

                        <div class="mb-4 flex items-center justify-between">
                            <label class="block text-gray-700 text-sm font-medium">Schedule Transaction</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" id="bulksms-schedule-toggle" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div id="bulksms-schedule-fields" class="mb-6 hidden">
                            <div class="mb-4">
                                <label for="bulksms-schedule-date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
                                <input type="date" id="bulksms-schedule-date" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-4">
                                <label for="bulksms-schedule-time" class="block text-gray-700 text-sm font-medium mb-2">Time</label>
                                <input type="time" id="bulksms-schedule-time" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Total Cost</label>
                            <p id="bulksms-total-cost" class="text-2xl font-bold text-gray-800">₦0.00</p>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Send SMS</button>
                    </form>
                </div>

                <!-- Gift Card Buy/Sell Form -->
                <div id="giftcard-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Gift Card Services</h3>
                    <form id="giftcard-form">
                        <div class="mb-4 flex items-center justify-center space-x-4">
                            <button type="button" id="giftcard-buy-btn" class="flex-1 p-3 rounded-lg font-semibold bg-blue-600 text-white shadow-md">Buy Gift Card</button>
                            <button type="button" id="giftcard-sell-btn" class="flex-1 p-3 rounded-lg font-semibold bg-gray-200 text-gray-700 shadow-md">Sell Gift Card</button>
                        </div>

                        <div class="mb-4">
                            <label for="giftcard-type" class="block text-gray-700 text-sm font-medium mb-2">Gift Card Type</label>
                            <select id="giftcard-type" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Gift Card</option>
                                <option value="amazon">Amazon</option>
                                <option value="apple">Apple iTunes</option>
                                <option value="google_play">Google Play</option>
                                <option value="steam">Steam</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="giftcard-denomination" class="block text-gray-700 text-sm font-medium mb-2">Denomination/Amount</label>
                            <input type="number" id="giftcard-denomination" placeholder="e.g., 50 (USD)" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" required>
                        </div>

                        <div id="giftcard-sell-details" class="hidden">
                            <div class="mb-4">
                                <label for="giftcard-code" class="block text-gray-700 text-sm font-medium mb-2">Gift Card Code</label>
                                <input type="text" id="giftcard-code" placeholder="Enter card code" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-6">
                                <label for="giftcard-image" class="block text-gray-700 text-sm font-medium mb-2">Upload Card Image (Optional)</label>
                                <input type="file" id="giftcard-image" accept="image/*" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Estimated Payout/Cost</label>
                            <p id="giftcard-estimated-value" class="text-2xl font-bold text-gray-800">₦0.00</p>
                        </div>

                        <button type="submit" id="giftcard-submit-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Proceed</button>
                    </form>
                </div>

                <!-- Print Recharge Card Form (New) -->
                <div id="recharge-card-form-section" class="hidden service-form">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Print Recharge Card</h3>
                    <form id="recharge-card-printing-form">
                        <div class="mb-4">
                            <label for="recharge-card-network" class="block text-gray-700 text-sm font-medium mb-2">Network</label>
                            <select id="recharge-card-network" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Network</option>
                                <option value="MTN">MTN</option>
                                <option value="Glo">Glo</option>
                                <option value="Airtel">Airtel</option>
                                <option value="9mobile">9mobile</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="recharge-card-amount" class="block text-gray-700 text-sm font-medium mb-2">Amount (₦)</label>
                            <select id="recharge-card-amount" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Amount</option>
                                <option value="100">₦100</option>
                                <option value="200">₦200</option>
                                <option value="500">₦500</option>
                                <option value="1000">₦1000</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="recharge-card-quantity" class="block text-gray-700 text-sm font-medium mb-2">Quantity</label>
                            <input type="number" id="recharge-card-quantity" placeholder="Number of cards" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" value="1" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Total Cost</label>
                            <p id="recharge-card-total-cost" class="text-2xl font-bold text-gray-800">₦0.00</p>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Print Cards</button>
                    </form>
                </div>

            </div>
        </div>

        <!-- Profile Page (New) -->
        <div id="profile-page" class="page flex-grow p-4 pb-20 md:p-8 max-w-screen-md mx-auto w-full">
            <div class="flex justify-between items-center mb-6">
                <button id="back-to-dashboard-from-profile" class="p-2 rounded-full bg-white shadow-md text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="text-2xl font-bold text-gray-800 text-center flex-grow">My Profile</h2>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Account Information</h3>
                <div class="space-y-3">
                    <p><span class="font-medium text-gray-700">Name:</span> <span id="profile-name">John Doe</span></p>
                    <p><span class="font-medium text-gray-700">Email:</span> <span id="profile-email">john.doe@example.com</span></p>
                    <p><span class="font-medium text-gray-700">Phone:</span> <span id="profile-phone">08012345678</span></p>
                    <p><span class="font-medium text-gray-700">Tier Level:</span> <span id="profile-tier" class="font-bold text-blue-600">Tier 1</span></p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Account Management</h3>
                <button id="upgrade-tier-btn" class="w-full bg-green-600 text-white p-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Upgrade to Tier 2
                </button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Security</h3>
                <div class="space-y-4">
                    <button id="reset-password-btn" class="w-full bg-red-500 text-white p-3 rounded-lg font-semibold hover:bg-red-600 transition-colors">
                        Reset Password
                    </button>
                    <button id="reset-passcode-btn" class="w-full bg-yellow-500 text-white p-3 rounded-lg font-semibold hover:bg-yellow-600 transition-colors">
                        Reset Passcode
                    </button>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">App Settings</h3>
                <div class="flex items-center justify-between">
                    <span class="text-gray-700 font-medium">Dark Mode</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" id="dark-mode-toggle" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <a href="logout.php" class="w-full bg-red-600 text-white p-3 rounded-lg font-semibold hover:bg-red-700 transition-colors mt-4 block text-center">Logout</a>
            </div>
        </div>

    </div>

    <!-- Mobile Footer Navigation (only visible on dashboard) -->
    <nav id="footer-nav" class="fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 p-2 z-50">
        <div class="flex justify-around items-center h-16">
            <button class="nav-item flex flex-col items-center text-blue-600" data-nav="home">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-medium">Home</span>
            </button>
            <button class="nav-item flex flex-col items-center text-gray-500 hover:text-blue-600 transition-colors" data-nav="services">
                <i class="fas fa-th-large text-xl mb-1"></i>
                <span class="text-xs font-medium">Services</span>
            </button>
            <button class="nav-item flex flex-col items-center text-gray-500 hover:text-blue-600 transition-colors relative" data-nav="notifications">
                <i class="fas fa-bell text-xl mb-1"></i>
                <span class="text-xs font-medium">Notifications</span>
                <span id="unread-notifications-dot" class="notification-dot hidden"></span>
            </button>
            <button class="nav-item flex flex-col items-center text-gray-500 hover:text-blue-600 transition-colors" data-nav="profile">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-medium">Profile</span>
            </button>
            <button id="chat-with-admin-btn" class="flex flex-col items-center text-gray-500 hover:text-blue-600 transition-colors">
                <i class="fas fa-comments text-xl mb-1"></i>
                <span class="text-xs font-medium">Chat Admin</span>
            </button>
        </div>
    </nav>

    <!-- More Services Modal -->
    <div id="more-services-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">All Services</h3>
                <button id="close-more-services-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="all-services-list" class="grid grid-cols-2 gap-4">
                <!-- Services will be dynamically loaded here -->
            </div>
        </div>
    </div>

    <!-- Phonebook Manager Modal -->
    <div id="phonebook-manager-modal" class="modal-overlay hidden">
        <div class="modal-content modal-content-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Phone Book Manager</h3>
                <button id="close-phonebook-manager-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Tabs for Add Contact, Manage Groups, Upload Contacts -->
            <div class="flex border-b border-gray-200 mb-4">
                <button class="tab-btn p-3 text-gray-600 font-medium hover:text-blue-600 border-b-2 border-transparent data-tab="add-contact">Add Contact</button>
                <button class="tab-btn p-3 text-gray-600 font-medium hover:text-blue-600 border-b-2 border-transparent" data-tab="manage-groups">Manage Groups</button>
                <button class="tab-btn p-3 text-gray-600 font-medium hover:text-blue-600 border-b-2 border-transparent" data-tab="upload-contacts">Upload Contacts</button>
            </div>

            <!-- Tab Content: Add Contact -->
            <div id="add-contact-tab" class="tab-content">
                <h4 class="text-xl font-semibold text-gray-700 mb-3">Add New Contact</h4>
                <div class="mb-4">
                    <label for="new-contact-name" class="block text-gray-700 text-sm font-medium mb-2">Contact Name</label>
                    <input type="text" id="new-contact-name" placeholder="e.g., John Doe" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="new-contact-number" class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
                    <input type="tel" id="new-contact-number" placeholder="e.g., 08012345678" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="add-contact-group-select" class="block text-gray-700 text-sm font-medium mb-2">Add to Group</label>
                    <select id="add-contact-group-select" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select existing group</option>
                        <!-- Groups will be loaded dynamically by JavaScript -->
                        <option value="new-group">Create New Group...</option>
                    </select>
                </div>
                <button id="add-single-contact-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Add Contact</button>
            </div>

            <!-- Tab Content: Manage Groups -->
            <div id="manage-groups-tab" class="tab-content hidden">
                <h4 class="text-xl font-semibold text-gray-700 mb-3">Manage Contact Groups</h4>
                <div class="mb-4">
                    <label for="new-group-name" class="block text-gray-700 text-sm font-medium mb-2">New Group Name</label>
                    <input type="text" id="new-group-name" placeholder="e.g., New Clients" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button id="createNewGroupBtn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors mb-4">Create Group</button>

                <h5 class="text-lg font-semibold text-gray-700 mb-2">Existing Groups</h5>
                <ul id="existing-groups-list" class="bg-gray-50 p-3 rounded-lg max-h-40 overflow-y-auto">
                    <!-- Existing groups will be listed here -->
                </ul>
            </div>

            <!-- Tab Content: Upload Contacts -->
            <div id="upload-contacts-tab" class="tab-content hidden">
                <h4 class="text-xl font-semibold text-gray-700 mb-3">Upload Contacts from File</h4>
                <div class="mb-4">
                    <label for="upload-file-group-select" class="block text-gray-700 text-sm font-medium mb-2">Add to Group</label>
                    <select id="upload-file-group-select" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select existing group or create new</option>
                        <!-- Groups will be loaded dynamically -->
                    </select>
                </div>
                <div class="mb-4">
                    <label for="contact-upload-file" class="block text-gray-700 text-sm font-medium mb-2">Upload .txt or .csv file</label>
                    <input type="file" id="contact-upload-file" accept=".txt,.csv" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">File should contain one phone number per line for .txt, or numbers in a column for .csv.</p>
                </div>
                <button id="upload-contacts-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Upload & Add Contacts</button>
            </div>
        </div>
    </div>

    <!-- Referral Details Modal (New) -->
    <div id="referral-details-modal" class="modal-overlay hidden">
        <div class="modal-content modal-content-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Referral Details</h3>
                <button id="close-referral-details-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-lg font-semibold text-gray-700 mb-2">Your Referral Link:</p>
                <div class="flex items-center space-x-2">
                    <input type="text" id="display-referral-link" class="flex-grow p-2 rounded-lg border border-gray-300 bg-gray-50 text-gray-700" value="" readonly>
                    <button id="copy-modal-referral-link-button" class="p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="text-xl font-semibold text-gray-700 mb-3">Referred Users</h4>
                <ul id="referred-users-list" class="bg-gray-50 p-3 rounded-lg max-h-60 overflow-y-auto">
                    <!-- Referred users will be listed here dynamically -->
                </ul>
            </div>

            <div class="text-right">
                <p class="text-xl font-bold text-gray-800">Total Bonus Earned: <span id="total-bonus-earned" class="text-green-600">₦0.00</span></p>
            </div>
        </div>
    </div>

    <!-- Transaction History Modal (New) -->
    <div id="transaction-history-modal" class="modal-overlay hidden">
        <div class="modal-content modal-content-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Transaction History</h3>
                <button id="close-transaction-history-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4 flex flex-col sm:flex-row gap-2">
                <input type="text" id="transaction-search-input" placeholder="Search transactions..." class="flex-grow p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button id="export-transactions-btn" class="bg-green-600 text-white p-2 rounded-lg font-semibold hover:bg-green-700 transition-colors flex-shrink-0">
                    <i class="fas fa-file-export mr-2"></i> Export All
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
                <ul id="transactions-list" class="divide-y divide-gray-100">
                    <!-- Transactions will be loaded here -->
                </ul>
            </div>

            <div class="flex justify-between items-center mt-4">
                <button id="prev-transactions-btn" class="bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <span id="transaction-page-info" class="text-gray-700 font-medium">Page 1 of 1</span>
                <button id="next-transactions-btn" class="bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal (New) -->
    <div id="transaction-details-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Transaction Details</h3>
                <button id="close-transaction-details-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="transaction-details-content" class="text-gray-700">
                <!-- Details will be loaded here -->
            </div>
            <button id="print-receipt-btn" class="mt-6 w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors" data-transaction-id="">
                <i class="fas fa-print mr-2"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- Transaction Calculator Modal (New) -->
    <div id="transaction-calculator-modal" class="modal-overlay hidden">
        <div class="modal-content modal-content-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Transaction Calculator</h3>
                <button id="close-transaction-calculator-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex flex-wrap justify-center gap-2 mb-4">
                <button class="time-filter-btn bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-200 transition-colors" data-period="today">Today</button>
                <button class="time-filter-btn bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-200 transition-colors" data-period="week">This Week</button>
                <button class="time-filter-btn bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-200 transition-colors" data-period="month">This Month</button>
                <button class="time-filter-btn bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-200 transition-colors" data-period="custom">Custom Range</button>
            </div>

            <div id="custom-date-range" class="hidden mb-4 p-4 bg-gray-50 rounded-lg">
                <label for="start-date" class="block text-gray-700 text-sm font-medium mb-2">Start Date:</label>
                <input type="date" id="start-date" class="w-full p-2 rounded-lg border border-gray-300 mb-2">
                <label for="end-date" class="block text-gray-700 text-sm font-medium mb-2">End Date:</label>
                <input type="date" id="end-date" class="w-full p-2 rounded-lg border border-gray-300">
                <button id="apply-custom-filter-btn" class="mt-4 w-full bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Apply Filter</button>
            </div>

            <div class="bg-white rounded-xl shadow-md p-4 mb-6">
                <h4 class="text-xl font-semibold text-gray-800 mb-3">Spending Summary</h4>
                <div id="spending-summary-list">
                    <!-- Spending summary will be loaded here -->
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 text-right">
                    <p class="text-xl font-bold text-gray-800">Total Spent: <span id="calculator-total-spent" class="text-red-600">₦0.00</span></p>
                </div>
            </div>

            <!-- New: Transactions in this Period Section -->
            <div class="bg-white rounded-xl shadow-md p-4">
                <h4 class="text-xl font-semibold text-gray-800 mb-3">Transactions in this Period</h4>
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-4">
                    <ul id="calculator-transactions-list" class="divide-y divide-gray-100">
                        <!-- Calculator transactions will be loaded here -->
                    </ul>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <button id="calculator-prev-transactions-btn" class="bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                    <span id="calculator-transaction-page-info" class="text-gray-700 font-medium">Page 1 of 1</span>
                    <button id="calculator-next-transactions-btn" class="bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Modal (New) -->
    <div id="notifications-modal" class="modal-overlay hidden">
        <div class="modal-content modal-content-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Notifications</h3>
                <button id="close-notifications-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <button id="mark-all-read-btn" class="w-full bg-blue-600 text-white p-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors mb-4">
                Mark All As Read
            </button>
            <ul id="notifications-list" class="bg-white rounded-xl shadow-md overflow-hidden divide-y divide-gray-100">
                <!-- Notifications will be loaded here -->
            </ul>
        </div>
    </div>

    <!-- BVN Verification Modal (New) -->
    <div id="bvn-verification-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">BVN Verification</h3>
                <button id="close-bvn-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-700 mb-4">Please enter your 11-digit BVN to upgrade to Tier 2.</p>
            <div class="mb-4">
                <label for="bvn-input" class="block text-gray-700 text-sm font-medium mb-2">BVN</label>
                <input type="text" id="bvn-input" placeholder="e.g., 12345678901" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="11" pattern="\d{11}" title="BVN must be 11 digits" required>
            </div>
            <button id="verify-bvn-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Verify BVN</button>
        </div>
    </div>

    <!-- Password Reset Modal (New) -->
    <div id="password-reset-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Reset Password</h3>
                <button id="close-password-reset-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-700 mb-4">Enter your new password.</p>
            <div class="mb-4">
                <label for="new-password-input" class="block text-gray-700 text-sm font-medium mb-2">New Password</label>
                <input type="password" id="new-password-input" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="confirm-password-input" class="block text-gray-700 text-sm font-medium mb-2">Confirm New Password</label>
                <input type="password" id="confirm-password-input" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <button id="confirm-password-reset-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Reset Password</button>
        </div>
    </div>

    <!-- Passcode Reset Modal (New) -->
    <div id="passcode-reset-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Reset Passcode</h3>
                <button id="close-passcode-reset-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-700 mb-4">Enter your new 4-digit passcode.</p>
            <div class="mb-4">
                <label for="new-passcode-input" class="block text-gray-700 text-sm font-medium mb-2">New Passcode</label>
                <input type="text" id="new-passcode-input" maxlength="4" pattern="\d{4}" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="confirm-passcode-input" class="block text-gray-700 text-sm font-medium mb-2">Confirm New Passcode</label>
                <input type="text" id="confirm-passcode-input" maxlength="4" pattern="\d{4}" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <button id="confirm-passcode-reset-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">Reset Passcode</button>
        </div>
    </div>

    <!-- Submit Payment Modal -->
    <div id="submit-payment-modal" class="modal-overlay hidden">
        <div class="modal-content modal-content-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Submit Payment Notification</h3>
                <button id="close-payment-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <!-- Bank Details -->
            <div id="bank-details-section" class="mb-6">
                <!-- Bank details will be loaded here dynamically -->
            </div>

            <form id="payment-notification-form" action="submit_payment.php" method="POST">
                <div class="mb-4">
                    <label for="bank_id" class="block text-gray-700 text-sm font-bold mb-2">Bank You Paid To</label>
                    <select id="bank_id" name="bank_id" class="w-full p-3 rounded-lg border border-gray-300" required>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="mb-4">
                    <label for="amount-paid" class="block text-gray-700 text-sm font-bold mb-2">Amount Paid</label>
                    <input type="number" id="amount-paid" name="amount" class="w-full p-3 rounded-lg border border-gray-300" required step="0.01">
                </div>
                <div class="mb-4">
                    <label for="payment-proof" class="block text-gray-700 text-sm font-bold mb-2">Payment Proof (e.g., Transaction ID)</label>
                    <textarea id="payment-proof" name="payment_proof" class="w-full p-3 rounded-lg border border-gray-300" required></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700">Submit Notification</button>
            </form>

            <div class="mt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Previous Orders</h3>
                <div id="previous-orders-section">
                    <!-- Previous orders will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div id="withdraw-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Withdraw Funds</h3>
                <button id="close-withdraw-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="withdraw-form" action="withdrawal_actions.php?action=request" method="POST">
                <div class="mb-4">
                    <label for="withdraw-amount" class="block text-gray-700 text-sm font-bold mb-2">Amount</label>
                    <input type="number" step="0.01" name="amount" id="withdraw-amount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                </div>
                <div class="mb-4">
                    <label for="withdraw-bank-details" class="block text-gray-700 text-sm font-bold mb-2">Bank Details</label>
                    <textarea name="bank_details" id="withdraw-bank-details" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
                    <p class="text-xs text-gray-600 mt-1">Please enter your Account Name, Account Number, and Bank Name.</p>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Request Bank Withdrawal</button>
                    <button type="button" id="withdraw-to-wallet-btn" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-2">Withdraw to Wallet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Share Fund Modal -->
    <div id="share-fund-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Share Fund</h3>
                <button id="close-share-fund-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="share-fund-form" action="share_fund.php" method="POST">
                <div class="mb-4">
                    <label for="recipient-email" class="block text-gray-700 text-sm font-bold mb-2">Recipient Email</label>
                    <input type="email" id="recipient-email" name="email" class="w-full p-3 rounded-lg border border-gray-300" required>
                </div>
                <div class="mb-4">
                    <label for="share-amount" class="block text-gray-700 text-sm font-bold mb-2">Amount</label>
                    <input type="number" id="share-amount" name="amount" class="w-full p-3 rounded-lg border border-gray-300" required step="0.01">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700">Share Fund</button>
            </form>
        </div>
    </div>

    <?php include 'chat.php'; ?>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/transactions.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>
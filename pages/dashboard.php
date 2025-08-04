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

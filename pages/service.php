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

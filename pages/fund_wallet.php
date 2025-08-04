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

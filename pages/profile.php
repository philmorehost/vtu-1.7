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

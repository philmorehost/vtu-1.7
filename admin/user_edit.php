<?php
require_once('../includes/session_config.php');
require_once('auth_check.php');
require_once('../includes/db.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: users.php');
    exit();
}
$userId = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit();
}

$title = 'Edit User: ' . htmlspecialchars($user['name']);
require_once('includes/header.php');
require_once('csrf_token.php');
?>

<!-- Edit Profile -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold mb-4">User Profile</h2>
    <form action="user_actions.php?action=update_profile&id=<?= $user['id'] ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tier</label>
                <input type="number" name="tier" value="<?= htmlspecialchars($user['tier']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Profile</button>
        </div>
    </form>
</div>

<!-- Credit/Debit Wallet -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold mb-4">Credit/Debit Wallet</h2>
    <p class="mb-2">Current Balance: <strong>₦<?= htmlspecialchars(number_format($user['wallet_balance'], 2)) ?></strong></p>
    <form action="user_actions.php?action=update_balance&id=<?= $user['id'] ?>" method="POST" class="flex flex-wrap items-center">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <select name="type" class="mr-2 border rounded py-2 px-3 mb-2 md:mb-0">
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
        </select>
        <input type="number" step="0.01" name="amount" placeholder="Amount" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 mr-2 mb-2 md:mb-0" required>
        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Submit</button>
    </form>
</div>

<!-- API Management -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold mb-4">API Management</h2>
    <div class="flex flex-wrap items-center mb-2">
        <p class="mr-2">API Key:</p>
        <input type="password" id="api-key-input" value="<?= htmlspecialchars($user['api_key'] ?: 'Not Generated') ?>" class="flex-grow shadow appearance-none border rounded py-2 px-3 text-gray-700 mb-2 md:mb-0" readonly>
        <button onclick="toggleApiKeyVisibility()" class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mb-2 md:mb-0">Show</button>
    </div>
    <div class="flex flex-wrap items-center space-x-4">
        <form action="user_actions.php?action=toggle_api&id=<?= $user['id'] ?>" method="POST" class="inline-block mb-2 md:mb-0">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="font-bold py-2 px-4 rounded <?= $user['api_enabled'] ? 'bg-yellow-500 hover:bg-yellow-700 text-white' : 'bg-gray-300 hover:bg-gray-400 text-black' ?>">
                <?= $user['api_enabled'] ? 'Disable API' : 'Enable API' ?>
            </button>
        </form>
         <form action="user_actions.php?action=generate_api_key&id=<?= $user['id'] ?>" method="POST" class="inline-block mb-2 md:mb-0">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Generate New Key
            </button>
        </form>
        <button onclick="copyApiKey()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Copy Key</button>
    </div>
</div>

<!-- Change Password -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold mb-4">Change Password</h2>
    <form action="user_actions.php?action=change_password&id=<?= $user['id'] ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Change Password</button>
        </div>
    </form>
</div>

<!-- User Status -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">User Status</h2>
    <div class="flex flex-wrap items-center space-x-4">
        <form action="user_actions.php?action=toggle_status&id=<?= $user['id'] ?>" method="POST" class="inline-block mb-2 md:mb-0">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="font-bold py-2 px-4 rounded <?= $user['status'] === 'active' ? 'bg-red-500 hover:bg-red-700 text-white' : 'bg-green-500 hover:bg-green-700 text-white' ?>">
                <?= $user['status'] === 'active' ? 'Suspend User' : 'Unsuspend User' ?>
            </button>
        </form>
        <form action="user_actions.php?action=delete&id=<?= $user['id'] ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="inline-block">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="bg-red-700 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                Delete User
            </button>
        </form>
    </div>
</div>

<script>
    function toggleApiKeyVisibility() {
        const apiKeyInput = document.getElementById('api-key-input');
        const button = event.target;
        if (apiKeyInput.type === 'password') {
            apiKeyInput.type = 'text';
            button.textContent = 'Hide';
        } else {
            apiKeyInput.type = 'password';
            button.textContent = 'Show';
        }
    }

    function copyApiKey() {
        const apiKey = document.getElementById('api-key-input').value;
        if (apiKey && apiKey !== 'Not Generated') {
            navigator.clipboard.writeText(apiKey).then(() => {
                alert('API Key copied to clipboard!');
            }, (err) => {
                alert('Failed to copy API Key.');
            });
        } else {
            alert('No API Key to copy.');
        }
    }
</script>

<?php require_once('includes/footer.php'); ?>

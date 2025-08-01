<?php
/**
 * API Gateway Manager
 * Central management interface for API providers and service routing
 */

$title = 'API Gateway Manager';
require_once('../includes/header.php');
require_once('../../includes/db.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_provider':
                // Validate required fields for adding API provider
                $requiredFields = ['name', 'display_name', 'base_url', 'auth_type', 'priority', 'status'];
                foreach ($requiredFields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                $stmt = $pdo->prepare("INSERT INTO api_providers (name, display_name, base_url, api_key, secret_key, auth_type, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['display_name'], 
                    $_POST['base_url'],
                    $_POST['api_key'] ?? null, // Handle null api_key gracefully
                    $_POST['secret_key'] ?? null, // Handle null secret_key gracefully
                    $_POST['auth_type'],
                    (int)$_POST['priority'],
                    $_POST['status']
                ]);
                echo json_encode(['success' => true, 'message' => 'API Provider added successfully']);
                exit;
                
            case 'update_provider':
                // Validate required fields for updating API provider
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    throw new Exception("Provider ID is required for update");
                }
                
                $stmt = $pdo->prepare("UPDATE api_providers SET display_name=?, base_url=?, api_key=?, secret_key=?, auth_type=?, priority=?, status=? WHERE id=?");
                $stmt->execute([
                    $_POST['display_name'],
                    $_POST['base_url'],
                    $_POST['api_key'] ?? null, // Handle null api_key gracefully
                    $_POST['secret_key'] ?? null, // Handle null secret_key gracefully
                    $_POST['auth_type'],
                    (int)$_POST['priority'],
                    $_POST['status'],
                    (int)$_POST['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'API Provider updated successfully']);
                exit;
                
            case 'delete_provider':
                $stmt = $pdo->prepare("DELETE FROM api_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'API Provider deleted successfully']);
                exit;
                
            case 'add_route':
                $stmt = $pdo->prepare("INSERT INTO api_routes (service_type, network_id, api_provider_id, endpoint, method, request_mapping, response_mapping, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['service_type'],
                    $_POST['network_id'] ?: null,
                    (int)$_POST['api_provider_id'],
                    $_POST['endpoint'],
                    $_POST['method'],
                    $_POST['request_mapping'],
                    $_POST['response_mapping'],
                    (int)$_POST['priority'],
                    $_POST['status']
                ]);
                echo json_encode(['success' => true, 'message' => 'API Route added successfully']);
                exit;
                
            case 'get_provider':
                $stmt = $pdo->prepare("SELECT * FROM api_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                $provider = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $provider]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Fetch API providers
try {
    $stmt = $pdo->query("SELECT * FROM api_providers ORDER BY priority DESC, name ASC");
    $providers = $stmt->fetchAll();
} catch (Exception $e) {
    $providers = [];
}

// Fetch Networks
try {
    $stmt = $pdo->query("SELECT * FROM networks ORDER BY name ASC");
    $networks = $stmt->fetchAll();
} catch (Exception $e) {
    $networks = [];
}

// Fetch API routes with provider and network info
try {
    $stmt = $pdo->query("
        SELECT ar.*, ap.display_name as provider_name, n.display_name as network_name 
        FROM api_routes ar 
        LEFT JOIN api_providers ap ON ar.api_provider_id = ap.id 
        LEFT JOIN networks n ON ar.network_id = n.id 
        ORDER BY ar.service_type, ar.priority DESC
    ");
    $routes = $stmt->fetchAll();
} catch (Exception $e) {
    $routes = [];
}
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">API Gateway Manager</h1>
        <div class="space-x-2">
            <button id="addProviderBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Add Provider
            </button>
            <button id="addRouteBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-route mr-2"></i>Add Route
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 mb-6">
        <button class="tab-btn active px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300" data-tab="providers">
            <i class="fas fa-server mr-2"></i>API Providers
        </button>
        <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300" data-tab="routes">
            <i class="fas fa-route mr-2"></i>API Routes
        </button>
        <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300" data-tab="networks">
            <i class="fas fa-network-wired mr-2"></i>Networks
        </button>
    </div>

    <!-- API Providers Tab -->
    <div id="providers-tab" class="tab-content">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-800">API Providers</h3>
                <p class="text-sm text-gray-600">Manage your API service providers and their configurations</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Base URL</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Auth Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($providers as $provider): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($provider['display_name']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($provider['name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($provider['base_url']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    <?= htmlspecialchars($provider['auth_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $provider['priority'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $provider['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $provider['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="edit-provider-btn text-blue-600 hover:text-blue-900" data-id="<?= $provider['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="delete-provider-btn text-red-600 hover:text-red-900" data-id="<?= $provider['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- API Routes Tab -->
    <div id="routes-tab" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-800">API Routes</h3>
                <p class="text-sm text-gray-600">Configure service routing to API providers</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Network</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Endpoint</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($routes as $route): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                    <?= ucfirst($route['service_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $route['network_name'] ?: 'All Networks' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($route['provider_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($route['endpoint']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $route['priority'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $route['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $route['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button class="edit-route-btn text-blue-600 hover:text-blue-900" data-id="<?= $route['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="delete-route-btn text-red-600 hover:text-red-900" data-id="<?= $route['id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Networks Tab -->
    <div id="networks-tab" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Networks</h3>
                <p class="text-sm text-gray-600">Manage telecom networks and providers</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Network</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($networks as $network): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($network['display_name']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($network['name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($network['code']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $network['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $network['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($network['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Provider Modal -->
<div id="providerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="providerModalTitle">Add API Provider</h3>
                <button id="closeProviderModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="providerForm">
                <input type="hidden" id="providerId" name="id">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provider Name</label>
                        <input type="text" id="providerName" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Display Name</label>
                        <input type="text" id="providerDisplayName" name="display_name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Base URL</label>
                        <input type="url" id="providerBaseUrl" name="base_url" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">API Key</label>
                            <input type="text" id="providerApiKey" name="api_key" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Secret Key</label>
                            <input type="password" id="providerSecretKey" name="secret_key" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Auth Type</label>
                            <select id="providerAuthType" name="auth_type" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="bearer">Bearer Token</option>
                                <option value="basic">Basic Auth</option>
                                <option value="api_key">API Key</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <input type="number" id="providerPriority" name="priority" value="1" min="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="providerStatus" name="status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancelProviderBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Route Modal -->
<div id="routeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add API Route</h3>
                <button id="closeRouteModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="routeForm">
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Service Type</label>
                            <select name="service_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Service</option>
                                <option value="data">Data</option>
                                <option value="airtime">Airtime</option>
                                <option value="cabletv">Cable TV</option>
                                <option value="electricity">Electricity</option>
                                <option value="exam">Exam Pin</option>
                                <option value="betting">Betting</option>
                                <option value="recharge">Recharge Card</option>
                                <option value="bulksms">Bulk SMS</option>
                                <option value="giftcard">Gift Card</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Network</label>
                            <select name="network_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Networks</option>
                                <?php foreach ($networks as $network): ?>
                                <option value="<?= $network['id'] ?>"><?= htmlspecialchars($network['display_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">API Provider</label>
                        <select name="api_provider_id" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Select Provider</option>
                            <?php foreach ($providers as $provider): ?>
                            <option value="<?= $provider['id'] ?>"><?= htmlspecialchars($provider['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Endpoint</label>
                            <input type="text" name="endpoint" required placeholder="/api/data" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Method</label>
                            <select name="method" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="POST">POST</option>
                                <option value="GET">GET</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Request Mapping (JSON)</label>
                        <textarea name="request_mapping" rows="3" placeholder='{"phone": "{phoneNumber}", "plan": "{planId}"}' class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Response Mapping (JSON)</label>
                        <textarea name="response_mapping" rows="3" placeholder='{"success": "status", "message": "description"}' class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <input type="number" name="priority" value="1" min="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancelRouteBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Route</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab functionality
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class from all tabs and content
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active', 'border-blue-500', 'text-blue-600'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        
        // Add active class to clicked tab
        btn.classList.add('active', 'border-blue-500', 'text-blue-600');
        
        // Show corresponding content
        const tabId = btn.getAttribute('data-tab') + '-tab';
        document.getElementById(tabId).classList.remove('hidden');
    });
});

// Set first tab as active
document.querySelector('.tab-btn').classList.add('border-blue-500', 'text-blue-600');

// Modal functionality
const providerModal = document.getElementById('providerModal');
const routeModal = document.getElementById('routeModal');

// Add Provider Modal
document.getElementById('addProviderBtn').addEventListener('click', () => {
    document.getElementById('providerModalTitle').textContent = 'Add API Provider';
    document.getElementById('providerForm').reset();
    document.getElementById('providerId').value = '';
    providerModal.classList.remove('hidden');
});

document.getElementById('closeProviderModal').addEventListener('click', () => {
    providerModal.classList.add('hidden');
});

document.getElementById('cancelProviderBtn').addEventListener('click', () => {
    providerModal.classList.add('hidden');
});

// Add Route Modal
document.getElementById('addRouteBtn').addEventListener('click', () => {
    document.getElementById('routeForm').reset();
    routeModal.classList.remove('hidden');
});

document.getElementById('closeRouteModal').addEventListener('click', () => {
    routeModal.classList.add('hidden');
});

document.getElementById('cancelRouteBtn').addEventListener('click', () => {
    routeModal.classList.add('hidden');
});

// Provider Form Submit
document.getElementById('providerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const providerId = document.getElementById('providerId').value;
    
    formData.append('action', providerId ? 'update_provider' : 'add_provider');
    
    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

// Route Form Submit
document.getElementById('routeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add_route');
    
    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

// Edit Provider
document.querySelectorAll('.edit-provider-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const providerId = btn.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'get_provider');
        formData.append('id', providerId);
        
        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                const provider = result.data;
                document.getElementById('providerModalTitle').textContent = 'Edit API Provider';
                document.getElementById('providerId').value = provider.id;
                document.getElementById('providerName').value = provider.name;
                document.getElementById('providerDisplayName').value = provider.display_name;
                document.getElementById('providerBaseUrl').value = provider.base_url;
                document.getElementById('providerApiKey').value = provider.api_key || '';
                document.getElementById('providerSecretKey').value = provider.secret_key || '';
                document.getElementById('providerAuthType').value = provider.auth_type;
                document.getElementById('providerPriority').value = provider.priority;
                document.getElementById('providerStatus').value = provider.status;
                providerModal.classList.remove('hidden');
            } else {
                alert('Error loading provider data');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
});

// Delete Provider
document.querySelectorAll('.delete-provider-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (confirm('Are you sure you want to delete this API provider?')) {
            const providerId = btn.getAttribute('data-id');
            const formData = new FormData();
            formData.append('action', 'delete_provider');
            formData.append('id', providerId);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    });
});
</script>

<?php require_once('../includes/footer.php'); ?>
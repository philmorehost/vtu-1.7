<?php
/**
 * Modular API Manager
 * Simplified admin interface for managing API providers using modules
 */

require_once('../../includes/db.php');
require_once('../../apis/ApiProviderRegistry.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_provider':
                $requiredFields = ['provider_module', 'api_key'];
                foreach ($requiredFields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Get provider info from module
                $provider = ApiProviderRegistry::getProvider($_POST['provider_module']);
                $providerInfo = $provider->getProviderInfo();
                
                $stmt = $pdo->prepare("
                    INSERT INTO api_providers 
                    (name, provider_module, display_name, base_url, api_key, secret_key, auth_type, priority, status, config_fields) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $providerInfo['name'],
                    $_POST['provider_module'],
                    $providerInfo['display_name'],
                    '',  // Base URL will be set by the module
                    $_POST['api_key'],
                    $_POST['secret_key'] ?? null,
                    'api_key',
                    (int)($_POST['priority'] ?? 1),
                    $_POST['status'] ?? 'active',
                    json_encode($provider->getRequiredConfig())
                ]);
                
                $providerId = $pdo->lastInsertId();
                
                // Add service routes
                if (isset($_POST['services']) && is_array($_POST['services'])) {
                    $routeStmt = $pdo->prepare("
                        INSERT INTO api_provider_routes 
                        (api_provider_id, service_type, network_id, priority, status) 
                        VALUES (?, ?, ?, ?, 'active')
                    ");
                    
                    foreach ($_POST['services'] as $service) {
                        $routeStmt->execute([
                            $providerId,
                            $service,
                            null, // For all networks initially
                            (int)($_POST['priority'] ?? 1)
                        ]);
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'API Provider added successfully']);
                exit;
                
            case 'update_provider':
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    throw new Exception("Provider ID is required for update");
                }
                
                $stmt = $pdo->prepare("
                    UPDATE api_providers 
                    SET api_key=?, secret_key=?, priority=?, status=? 
                    WHERE id=?
                ");
                $stmt->execute([
                    $_POST['api_key'],
                    $_POST['secret_key'] ?? null,
                    (int)($_POST['priority'] ?? 1),
                    $_POST['status'] ?? 'active',
                    (int)$_POST['id']
                ]);
                
                echo json_encode(['success' => true, 'message' => 'API Provider updated successfully']);
                exit;
                
            case 'delete_provider':
                $stmt = $pdo->prepare("DELETE FROM api_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'API Provider deleted successfully']);
                exit;
                
            case 'test_provider':
                $stmt = $pdo->prepare("SELECT * FROM api_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                $providerConfig = $stmt->fetch();
                
                if (!$providerConfig) {
                    throw new Exception("Provider not found");
                }
                
                $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                    'api_key' => $providerConfig['api_key'],
                    'secret_key' => $providerConfig['secret_key'],
                    'base_url' => $providerConfig['base_url']
                ]);
                
                $result = $provider->checkBalance();
                echo json_encode(['success' => true, 'message' => 'Test completed', 'data' => $result]);
                exit;

            case 'get_provider':
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    throw new Exception("Provider ID is required");
                }
                $stmt = $pdo->prepare("SELECT * FROM api_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                $providerData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$providerData) {
                    throw new Exception("Provider not found");
                }
                echo json_encode(['success' => true, 'data' => $providerData]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Fetch configured API providers
try {
    $stmt = $pdo->query("
        SELECT ap.*, 
               GROUP_CONCAT(apr.service_type) as services
        FROM api_providers ap 
        LEFT JOIN api_provider_routes apr ON ap.id = apr.api_provider_id AND apr.status = 'active'
        WHERE ap.provider_module IS NOT NULL
        GROUP BY ap.id
        ORDER BY ap.priority DESC, ap.display_name ASC
    ");
    $providers = $stmt->fetchAll();
} catch (Exception $e) {
    $providers = [];
}

// Get available provider modules
$availableProviders = ApiProviderRegistry::getProviderList();

$title = 'Modular API Manager';
require_once('../includes/header.php');
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Modular API Manager</h1>
        <button id="addProviderBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add API Provider
        </button>
    </div>

    <!-- Info Card -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Simplified API Management</h3>
                <div class="text-sm text-blue-700">
                    <p>No more complex JSON mappings! Simply select a provider, enter your API credentials, and choose services.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- API Providers Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Configured API Providers</h3>
            <p class="text-sm text-gray-600">Manage your pre-integrated API providers with simple configuration</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Module</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Services</th>
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
                            <div class="text-sm text-gray-500">API Key: <?= htmlspecialchars(substr($provider['api_key'] ?? '', 0, 8)) ?>...</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                <?= htmlspecialchars($provider['provider_module']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php if ($provider['services']): ?>
                                    <?php foreach (explode(',', $provider['services']) as $service): ?>
                                        <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded mr-1 mb-1">
                                            <?= ucfirst($service) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-gray-500">No services configured</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $provider['priority'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $provider['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ucfirst($provider['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="edit-provider-btn text-blue-600 hover:text-blue-900" data-id="<?= $provider['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="test-provider-btn text-green-600 hover:text-green-900" data-id="<?= $provider['id'] ?>">
                                <i class="fas fa-vial"></i>
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

<!-- Add/Edit Provider Modal -->
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
                        <label class="block text-sm font-medium text-gray-700">API Provider Module</label>
                        <select id="providerModule" name="provider_module" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Select Provider</option>
                            <?php foreach ($availableProviders as $key => $name): ?>
                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pre-integrated provider modules - no JSON configuration needed!</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">API Key</label>
                            <input type="text" id="providerApiKey" name="api_key" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Secret Key</label>
                            <input type="password" id="providerSecretKey" name="secret_key" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supported Services</label>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="airtime" class="mr-2">
                                <span class="text-sm">Airtime</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="data" class="mr-2">
                                <span class="text-sm">Data</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="cable_tv" class="mr-2">
                                <span class="text-sm">Cable TV</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="electricity" class="mr-2">
                                <span class="text-sm">Electricity</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="exam" class="mr-2">
                                <span class="text-sm">Exam Pin</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="bulk_sms" class="mr-2">
                                <span class="text-sm">Bulk SMS</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="recharge_card" class="mr-2">
                                <span class="text-sm">Recharge Card</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="gift_card" class="mr-2">
                                <span class="text-sm">Gift Card</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="services[]" value="betting" class="mr-2">
                                <span class="text-sm">Betting</span>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <input type="number" id="providerPriority" name="priority" value="1" min="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="providerStatus" name="status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
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

<script>
// Modal functionality
const providerModal = document.getElementById('providerModal');

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

// Edit Provider
document.querySelectorAll('.edit-provider-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const providerId = btn.dataset.id;
        const formData = new FormData();
        formData.append('action', 'get_provider');
        formData.append('id', providerId);

        try {
            const response = await fetch('', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                document.getElementById('providerModalTitle').textContent = 'Edit API Provider';
                document.getElementById('providerId').value = data.id;
                document.getElementById('providerModule').value = data.provider_module;
                document.getElementById('providerModule').disabled = true; // Can't change module
                document.getElementById('providerApiKey').value = data.api_key;
                document.getElementById('providerSecretKey').value = data.secret_key || '';
                document.getElementById('providerPriority').value = data.priority;
                document.getElementById('providerStatus').value = data.status;

                // Uncheck all services first
                document.querySelectorAll('input[name="services[]"]').forEach(cb => cb.checked = false);

                // This part is tricky as services are on a different table.
                // For a complete solution, an additional query would be needed to get services for this provider.
                // For now, we leave it as is, and the admin can re-select services on update if needed.

                providerModal.classList.remove('hidden');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred: ' + error.message);
        }
    });
});

// Test Provider
document.querySelectorAll('.test-provider-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const providerId = btn.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'test_provider');
        formData.append('id', providerId);
        
        try {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert('Test successful: ' + JSON.stringify(result.data, null, 2));
            } else {
                alert('Test failed: ' + result.message);
            }
        } catch (error) {
            alert('Test error: ' + error.message);
        } finally {
            btn.innerHTML = '<i class="fas fa-vial"></i>';
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
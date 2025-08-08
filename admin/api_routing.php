<?php
require_once('../includes/db.php'); // Moved up for POST handling

// --- Handle POST requests for adding/updating routes ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add_route':
                $stmt = $pdo->prepare(
                    "INSERT INTO api_provider_routes (service_type, network_id, api_provider_id, priority, status) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $_POST['service_type'],
                    $_POST['network_id'] ?: null,
                    $_POST['api_provider_id'],
                    (int)$_POST['priority'],
                    $_POST['status']
                ]);
                break;
            case 'delete_route':
                $stmt = $pdo->prepare("DELETE FROM api_provider_routes WHERE id = ?");
                $stmt->execute([(int)$_POST['id']]);
                break;
        }
        // Redirect to avoid form resubmission
        header("Location: api_routing.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$title = 'Modular API Routing';
require_once('includes/header.php');

if (isset($error_message)) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error:</strong>
            <span class="block sm:inline">' . $error_message . '</span>
          </div>';
}

// --- Fetch data for the page ---
try {
    // Fetch all existing routes
    $routesStmt = $pdo->query("
        SELECT r.id, r.service_type, r.priority, r.status,
               n.display_name as network_name,
               p.display_name as provider_name
        FROM api_provider_routes r
        LEFT JOIN networks n ON r.network_id = n.id
        JOIN api_providers p ON r.api_provider_id = p.id
        ORDER BY r.service_type, n.display_name, r.priority DESC
    ");
    $routes = $routesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch networks for the form dropdown
    $networks = $pdo->query("SELECT id, display_name FROM networks ORDER BY display_name")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch API providers for the form dropdown
    $providers = $pdo->query("SELECT id, display_name FROM api_providers WHERE status = 'active' ORDER BY display_name")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$serviceTypes = ['data', 'airtime', 'cabletv', 'electricity', 'exam', 'betting', 'bulksms', 'recharge', 'giftcard'];

?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Modular API Routing</h1>
    <button id="addRouteBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Add New Route
    </button>
</div>

<!-- Info Card -->
<div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
    <p class="text-sm text-blue-700">
        Use this page to direct specific services (like MTN Data) to a preferred API provider. The system will use the route with the highest priority number.
    </p>
</div>

<!-- Routing Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Configured API Routes</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Network</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">API Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($routes as $route): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= htmlspecialchars(ucfirst($route['service_type'])) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($route['network_name'] ?? 'All Networks') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($route['provider_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($route['priority']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= $route['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ucfirst($route['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form action="api_routing.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this route?');">
                            <input type="hidden" name="action" value="delete_route">
                            <input type="hidden" name="id" value="<?= $route['id'] ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                 <?php if (empty($routes)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No routes configured yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Add Route Modal -->
<div id="addRouteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <form action="api_routing.php" method="POST">
            <input type="hidden" name="action" value="add_route">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New API Route</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Service Type</label>
                    <select id="serviceTypeSelect" name="service_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <?php foreach ($serviceTypes as $type): ?>
                        <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="networkDropdownContainer">
                    <label class="block text-sm font-medium text-gray-700">Network</label>
                    <select name="network_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">All Networks</option>
                        <?php foreach ($networks as $network): ?>
                        <option value="<?= $network['id'] ?>"><?= htmlspecialchars($network['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">API Provider</label>
                    <select name="api_provider_id" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">Select Provider</option>
                        <?php foreach ($providers as $provider): ?>
                        <option value="<?= $provider['id'] ?>"><?= htmlspecialchars($provider['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                    <input type="number" name="priority" value="10" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Higher number means higher priority.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Route</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addRouteModal = document.getElementById('addRouteModal');
    const addRouteBtn = document.getElementById('addRouteBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const serviceTypeSelect = document.getElementById('serviceTypeSelect');
    const networkDropdownContainer = document.getElementById('networkDropdownContainer');

    const servicesWithNetwork = ['data', 'airtime', 'bulksms', 'recharge'];

    function toggleNetworkDropdown() {
        const selectedService = serviceTypeSelect.value;
        if (servicesWithNetwork.includes(selectedService)) {
            networkDropdownContainer.style.display = 'block';
        } else {
            networkDropdownContainer.style.display = 'none';
        }
    }

    addRouteBtn.addEventListener('click', () => {
        addRouteModal.classList.remove('hidden');
        toggleNetworkDropdown(); // Set initial state when modal opens
    });

    cancelBtn.addEventListener('click', () => {
        addRouteModal.classList.add('hidden');
    });

    serviceTypeSelect.addEventListener('change', toggleNetworkDropdown);
});
</script>

<?php require_once('includes/footer.php'); ?>

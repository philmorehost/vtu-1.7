<?php
$title = 'Modular API Routing';
require_once('../includes/header.php');
require_once('../../includes/db.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'add_route':
                $stmt = $pdo->prepare("INSERT INTO modular_api_routes (service_product_id, api_provider_id, priority, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['service_product_id'], $_POST['api_provider_id'], $_POST['priority'], $_POST['status']]);
                echo json_encode(['success' => true, 'message' => 'Route added successfully']);
                exit;
            case 'update_route':
                $stmt = $pdo->prepare("UPDATE modular_api_routes SET service_product_id=?, api_provider_id=?, priority=?, status=? WHERE id=?");
                $stmt->execute([$_POST['service_product_id'], $_POST['api_provider_id'], $_POST['priority'], $_POST['status'], $_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Route updated successfully']);
                exit;
            case 'delete_route':
                $stmt = $pdo->prepare("DELETE FROM modular_api_routes WHERE id=?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Route deleted successfully']);
                exit;
            case 'get_route':
                $stmt = $pdo->prepare("SELECT * FROM modular_api_routes WHERE id=?");
                $stmt->execute([$_POST['id']]);
                $route = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $route]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}


// Fetch service types
try {
    $stmt = $pdo->query("SELECT DISTINCT type FROM service_products ORDER BY type ASC");
    $serviceTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $serviceTypes = [];
}

// Fetch service products
try {
    $stmt = $pdo->query("SELECT id, name, type FROM service_products ORDER BY type, name ASC");
    $serviceProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $serviceProducts = [];
}

// Fetch API providers
try {
    $stmt = $pdo->query("SELECT id, display_name FROM api_providers WHERE provider_module IS NOT NULL ORDER BY display_name ASC");
    $apiProviders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $apiProviders = [];
}

// Fetch existing routes
try {
    $stmt = $pdo->query("
        SELECT mr.*, sp.name as product_name, sp.type as service_type, ap.display_name as provider_name
        FROM modular_api_routes mr
        JOIN service_products sp ON mr.service_product_id = sp.id
        JOIN api_providers ap ON mr.api_provider_id = ap.id
        ORDER BY sp.type, sp.name, mr.priority DESC
    ");
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $routes = [];
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Modular API Routing</h1>
        <button id="add-route-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Route
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Configured Routes</h3>
            <p class="text-sm text-gray-600">Manage how services are routed to different API providers.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($routes as $route): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($route['service_type']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($route['product_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($route['provider_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($route['priority']) ?></td>
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

<!-- Add/Edit Route Modal -->
<div id="route-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="route-modal-title">Add Route</h3>
                <button id="close-route-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="route-form">
                <input type="hidden" id="route-id" name="id">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Service Type</label>
                        <select id="service-type" name="service_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Select Service</option>
                            <?php foreach ($serviceTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars(ucfirst($type)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Service Product</label>
                        <select id="service-product" name="service_product_id" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Select Product</option>
                            <!-- Products will be loaded here dynamically based on service type -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">API Provider</label>
                        <select id="api-provider" name="api_provider_id" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Select Provider</option>
                            <?php foreach ($apiProviders as $provider): ?>
                                <option value="<?= $provider['id'] ?>"><?= htmlspecialchars($provider['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <input type="number" id="priority" name="priority" value="1" min="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancel-route-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Route</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const serviceProducts = <?= json_encode($serviceProducts) ?>;

    const routeModal = document.getElementById('route-modal');
    const addRouteBtn = document.getElementById('add-route-btn');
    const closeRouteModalBtn = document.getElementById('close-route-modal');
    const cancelRouteBtn = document.getElementById('cancel-route-btn');
    const routeForm = document.getElementById('route-form');
    const serviceTypeSelect = document.getElementById('service-type');
    const serviceProductSelect = document.getElementById('service-product');
    const routeIdInput = document.getElementById('route-id');
    const routeModalTitle = document.getElementById('route-modal-title');

    addRouteBtn.addEventListener('click', () => {
        routeModalTitle.textContent = 'Add Route';
        routeForm.reset();
        routeIdInput.value = '';
        routeModal.classList.remove('hidden');
    });

    closeRouteModalBtn.addEventListener('click', () => {
        routeModal.classList.add('hidden');
    });

    cancelRouteBtn.addEventListener('click', () => {
        routeModal.classList.add('hidden');
    });

    serviceTypeSelect.addEventListener('change', () => {
        const selectedType = serviceTypeSelect.value;
        serviceProductSelect.innerHTML = '<option value="">Select Product</option>';
        if (selectedType) {
            const filteredProducts = serviceProducts.filter(p => p.type === selectedType);
            filteredProducts.forEach(p => {
                const option = document.createElement('option');
                option.value = p.id;
                option.textContent = p.name;
                serviceProductSelect.appendChild(option);
            });
        }
    });

    routeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(routeForm);
        const routeId = routeIdInput.value;
        formData.append('action', routeId ? 'update_route' : 'add_route');

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
            alert('An error occurred: ' + error.message);
        }
    });

    document.querySelectorAll('.edit-route-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const routeId = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'get_route');
            formData.append('id', routeId);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    const route = result.data;
                    routeModalTitle.textContent = 'Edit Route';
                    routeIdInput.value = route.id;

                    const product = serviceProducts.find(p => p.id == route.service_product_id);
                    if(product) {
                        serviceTypeSelect.value = product.type;

                        // Manually trigger change to populate products
                        const event = new Event('change');
                        serviceTypeSelect.dispatchEvent(event);

                        // Set product after a short delay to allow population
                        setTimeout(() => {
                           serviceProductSelect.value = route.service_product_id;
                        }, 100);
                    }

                    document.getElementById('api-provider').value = route.api_provider_id;
                    document.getElementById('priority').value = route.priority;
                    document.getElementById('status').value = route.status;
                    routeModal.classList.remove('hidden');
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        });
    });

    document.querySelectorAll('.delete-route-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (confirm('Are you sure you want to delete this route?')) {
                const routeId = btn.dataset.id;
                const formData = new FormData();
                formData.append('action', 'delete_route');
                formData.append('id', routeId);

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
                    alert('An error occurred: ' + error.message);
                }
            }
        });
    });
</script>

<?php require_once('../includes/footer.php'); ?>

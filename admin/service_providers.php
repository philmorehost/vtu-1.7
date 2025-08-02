<?php
/**
 * Service Provider Manager
 * Interface for managing non-network service providers (e.g., Betting, Cable TV)
 */
$title = 'Service Provider Manager';
require_once(__DIR__ . '/../includes/session_config.php');
require_once(__DIR__ . '/auth_check.php');
require_once(__DIR__ . '/../includes/db.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'add_provider':
                $stmt = $pdo->prepare("INSERT INTO service_providers (service_type, name, code, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['service_type'],
                    $_POST['name'],
                    $_POST['code'],
                    $_POST['status']
                ]);
                echo json_encode(['success' => true, 'message' => 'Service Provider added successfully']);
                exit;

            case 'update_provider':
                $stmt = $pdo->prepare("UPDATE service_providers SET service_type=?, name=?, code=?, status=? WHERE id=?");
                $stmt->execute([
                    $_POST['service_type'],
                    $_POST['name'],
                    $_POST['code'],
                    $_POST['status'],
                    (int)$_POST['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Service Provider updated successfully']);
                exit;

            case 'delete_provider':
                $stmt = $pdo->prepare("DELETE FROM service_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Service Provider deleted successfully']);
                exit;

            case 'get_provider':
                $stmt = $pdo->prepare("SELECT * FROM service_providers WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                $provider = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $provider]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

require_once(__DIR__ . '/includes/header.php');

// Fetch all service providers
try {
    $stmt = $pdo->query("SELECT * FROM service_providers ORDER BY service_type, name ASC");
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $providers = [];
}
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($title) ?></h1>
        <button id="addProviderBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Provider
        </button>
    </div>

    <!-- Providers Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800">All Service Providers</h3>
            <p class="text-sm text-gray-600">Manage providers for services like Betting, Cable TV, Electricity etc.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provider Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provider Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Service Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($providers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">No service providers found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($providers as $provider): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($provider['name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($provider['code']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                <?= htmlspecialchars(ucfirst($provider['service_type'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $provider['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= htmlspecialchars($provider['status']) ?>
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

<!-- Add/Edit Provider Modal -->
<div id="providerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="providerModalTitle">Add Service Provider</h3>
                <button id="closeProviderModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="providerForm">
                <input type="hidden" id="providerId" name="id">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Service Type</label>
                        <select id="providerServiceType" name="service_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Select Service</option>
                            <option value="airtime">Airtime</option>
                            <option value="data">Data</option>
                            <option value="cabletv">Cable TV</option>
                            <option value="electricity">Electricity</option>
                            <option value="exam">Exam Pin</option>
                            <option value="betting">Betting</option>
                            <option value="bulksms">Bulk SMS</option>
                            <option value="giftcard">Gift Card</option>
                            <option value="recharge">Recharge Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provider Name</label>
                        <input type="text" id="providerName" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., DStv, Eko Electric">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provider Code</label>
                        <input type="text" id="providerCode" name="code" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., dstv, eko-electric">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const providerModal = document.getElementById('providerModal');
    const addProviderBtn = document.getElementById('addProviderBtn');
    const closeProviderModalBtn = document.getElementById('closeProviderModal');
    const cancelProviderBtn = document.getElementById('cancelProviderBtn');
    const providerForm = document.getElementById('providerForm');
    const providerModalTitle = document.getElementById('providerModalTitle');
    const providerIdInput = document.getElementById('providerId');

    const openModal = (provider = null) => {
        providerForm.reset();
        if (provider) {
            providerModalTitle.textContent = 'Edit Service Provider';
            providerIdInput.value = provider.id;
            document.getElementById('providerServiceType').value = provider.service_type;
            document.getElementById('providerName').value = provider.name;
            document.getElementById('providerCode').value = provider.code;
            document.getElementById('providerStatus').value = provider.status;
        } else {
            providerModalTitle.textContent = 'Add Service Provider';
            providerIdInput.value = '';
        }
        providerModal.classList.remove('hidden');
    };

    const closeModal = () => {
        providerModal.classList.add('hidden');
    };

    addProviderBtn.addEventListener('click', () => openModal());
    closeProviderModalBtn.addEventListener('click', closeModal);
    cancelProviderBtn.addEventListener('click', closeModal);

    providerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(providerForm);
        const providerId = providerIdInput.value;
        formData.append('action', providerId ? 'update_provider' : 'add_provider');

        try {
            const response = await fetch('', { method: 'POST', body: formData });
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

    document.querySelectorAll('.edit-provider-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const formData = new FormData();
            formData.append('action', 'get_provider');
            formData.append('id', id);

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    openModal(result.data);
                } else {
                    alert('Error fetching provider details.');
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        });
    });

    document.querySelectorAll('.delete-provider-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (confirm('Are you sure you want to delete this provider?')) {
                const id = btn.dataset.id;
                const formData = new FormData();
                formData.append('action', 'delete_provider');
                formData.append('id', id);

                try {
                    const response = await fetch('', { method: 'POST', body: formData });
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
});
</script>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>

<?php
$title = 'Manage Services';
require_once('includes/header.php');
require_once('../includes/db.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_product':
                $stmt = $pdo->prepare("INSERT INTO service_products (service_type, network_id, name, plan_code, amount, selling_price, discount_percentage, validity, data_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['service_type'],
                    $_POST['network_id'] ?: null,
                    $_POST['name'],
                    $_POST['plan_code'],
                    (float)$_POST['amount'],
                    (float)$_POST['selling_price'],
                    (float)$_POST['discount_percentage'],
                    $_POST['validity'],
                    $_POST['data_size'],
                    $_POST['status']
                ]);
                echo json_encode(['success' => true, 'message' => 'Service product added successfully']);
                exit;
                
            case 'update_product':
                $stmt = $pdo->prepare("UPDATE service_products SET name=?, plan_code=?, amount=?, selling_price=?, discount_percentage=?, validity=?, data_size=?, status=? WHERE id=?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['plan_code'],
                    (float)$_POST['amount'],
                    (float)$_POST['selling_price'],
                    (float)$_POST['discount_percentage'],
                    $_POST['validity'],
                    $_POST['data_size'],
                    $_POST['status'],
                    (int)$_POST['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Service product updated successfully']);
                exit;
                
            case 'delete_product':
                $stmt = $pdo->prepare("DELETE FROM service_products WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Service product deleted successfully']);
                exit;
                
            case 'get_product':
                $stmt = $pdo->prepare("SELECT * FROM service_products WHERE id=?");
                $stmt->execute([(int)$_POST['id']]);
                $product = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $product]);
                exit;
                
            case 'toggle_view':
                $_SESSION['services_view'] = $_POST['view_type'];
                echo json_encode(['success' => true]);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Get current view mode (grid or list)
$viewMode = $_SESSION['services_view'] ?? 'list';

// Fetch service products with network information
try {
    $stmt = $pdo->query("
        SELECT sp.*, n.display_name as network_name, n.name as network_code 
        FROM service_products sp 
        LEFT JOIN networks n ON sp.network_id = n.id 
        ORDER BY sp.service_type, sp.network_id, sp.name ASC
    ");
    $allProducts = $stmt->fetchAll();
} catch (Exception $e) {
    $allProducts = [];
}

// Group products by service type
$services = [];
foreach ($allProducts as $product) {
    $services[$product['service_type']][] = $product;
}

// Fetch networks for dropdowns
try {
    $stmt = $pdo->query("SELECT * FROM networks ORDER BY name ASC");
    $networks = $stmt->fetchAll();
} catch (Exception $e) {
    $networks = [];
}
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Services</h1>
    <div class="flex space-x-2">
        <div class="flex bg-gray-200 rounded-lg p-1">
            <button id="listViewBtn" class="px-3 py-1 rounded <?= $viewMode === 'list' ? 'bg-white shadow' : '' ?>" onclick="toggleView('list')">
                <i class="fas fa-list"></i> List
            </button>
            <button id="gridViewBtn" class="px-3 py-1 rounded <?= $viewMode === 'grid' ? 'bg-white shadow' : '' ?>" onclick="toggleView('grid')">
                <i class="fas fa-th-large"></i> Grid
            </button>
        </div>
        <button id="addProductBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Product
        </button>
    </div>
</div>

<!-- Service Type Tabs -->
<div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
    <button class="tab-btn active px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="data">
        <i class="fas fa-wifi mr-2"></i>Data Plans
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="airtime">
        <i class="fas fa-phone mr-2"></i>Airtime
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="cabletv">
        <i class="fas fa-tv mr-2"></i>Cable TV
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="electricity">
        <i class="fas fa-bolt mr-2"></i>Electricity
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="exam">
        <i class="fas fa-graduation-cap mr-2"></i>Exam Pin
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="betting">
        <i class="fas fa-dice mr-2"></i>Betting
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="bulksms">
        <i class="fas fa-sms mr-2"></i>Bulk SMS
    </button>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 whitespace-nowrap" data-tab="giftcard">
        <i class="fas fa-gift mr-2"></i>Gift Card
    </button>
</div>

<?php foreach (['data', 'airtime', 'cabletv', 'electricity', 'exam', 'betting', 'bulksms', 'giftcard'] as $serviceType): ?>
<!-- <?= ucfirst($serviceType) ?> Tab -->
<div id="<?= $serviceType ?>-tab" class="tab-content <?= $serviceType !== 'data' ? 'hidden' : '' ?>">
    <?php if ($viewMode === 'list'): ?>
    <!-- List View -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-semibold text-gray-800"><?= ucfirst($serviceType) ?> Products</h3>
            <p class="text-sm text-gray-600">Manage <?= $serviceType ?> products and pricing</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Network</th>
                        <?php if ($serviceType === 'data'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Validity</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cost Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Selling Price</th>
                        <?php if ($serviceType === 'airtime'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Discount</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $products = $services[$serviceType] ?? [];
                    foreach ($products as $product): 
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($product['plan_code']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $product['network_name'] ?: 'All Networks' ?>
                        </td>
                        <?php if ($serviceType === 'data'): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($product['data_size']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($product['validity']) ?></td>
                        <?php endif; ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₦<?= number_format($product['amount'], 2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₦<?= number_format($product['selling_price'], 2) ?></td>
                        <?php if ($serviceType === 'airtime'): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $product['discount_percentage'] ?>%</td>
                        <?php endif; ?>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $product['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="edit-product-btn text-blue-600 hover:text-blue-900" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-product-btn text-red-600 hover:text-red-900" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="<?= $serviceType === 'data' ? '8' : ($serviceType === 'airtime' ? '7' : '6') ?>" class="px-6 py-4 text-center text-gray-500">
                            No <?= $serviceType ?> products found. <button class="text-blue-600 hover:text-blue-800 add-product-btn" data-service="<?= $serviceType ?>">Add one now</button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <!-- Grid View -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php 
        $products = $services[$serviceType] ?? [];
        foreach ($products as $product): 
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-sm text-gray-500"><?= $product['network_name'] ?: 'All Networks' ?></p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $product['status'] ?>
                    </span>
                </div>
                
                <?php if ($serviceType === 'data'): ?>
                <div class="mb-4">
                    <div class="text-sm text-gray-600 mb-1">Data Size: <?= htmlspecialchars($product['data_size']) ?></div>
                    <div class="text-sm text-gray-600">Validity: <?= htmlspecialchars($product['validity']) ?></div>
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Cost Price:</span>
                        <span>₦<?= number_format($product['amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between text-lg font-semibold text-gray-900">
                        <span>Selling Price:</span>
                        <span>₦<?= number_format($product['selling_price'], 2) ?></span>
                    </div>
                    <?php if ($serviceType === 'airtime' && $product['discount_percentage'] > 0): ?>
                    <div class="text-sm text-green-600 mt-1">
                        <?= $product['discount_percentage'] ?>% Discount
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-between items-center">
                    <button class="edit-product-btn text-blue-600 hover:text-blue-800 text-sm" data-id="<?= $product['id'] ?>">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="delete-product-btn text-red-600 hover:text-red-800 text-sm" data-id="<?= $product['id'] ?>">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($products)): ?>
        <div class="col-span-full bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 mb-4">No <?= $serviceType ?> products found</p>
            <button class="add-product-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700" data-service="<?= $serviceType ?>">
                <i class="fas fa-plus mr-2"></i>Add First Product
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="productModalTitle">Add Service Product</h3>
                <button id="closeProductModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="productForm">
                <input type="hidden" id="productId" name="id">
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Service Type</label>
                            <select id="productServiceType" name="service_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
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
                            <select id="productNetwork" name="network_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">All Networks</option>
                                <?php foreach ($networks as $network): ?>
                                <option value="<?= $network['id'] ?>"><?= htmlspecialchars($network['display_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" id="productName" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Plan Code</label>
                        <input type="text" id="productPlanCode" name="plan_code" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="API specific plan code">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost Price (₦)</label>
                            <input type="number" id="productAmount" name="amount" step="0.01" min="0" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selling Price (₦)</label>
                            <input type="number" id="productSellingPrice" name="selling_price" step="0.01" min="0" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Discount Percentage (%)</label>
                            <input type="number" id="productDiscount" name="discount_percentage" step="0.01" min="0" max="100" value="0" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="productStatus" name="status" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <!-- Fields for Data Plans -->
                    <div id="dataFields" class="grid grid-cols-2 gap-4 hidden">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Size</label>
                            <input type="text" id="productDataSize" name="data_size" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., 1GB, 500MB">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Validity</label>
                            <input type="text" id="productValidity" name="validity" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., 30 days, 1 month">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancelProductBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// View toggle functionality
function toggleView(viewType) {
    const formData = new FormData();
    formData.append('action', 'toggle_view');
    formData.append('view_type', viewType);
    
    fetch('', {
        method: 'POST',
        body: formData
    }).then(() => {
        window.location.reload();
    });
}

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
const productModal = document.getElementById('productModal');

// Add Product Modal
document.getElementById('addProductBtn').addEventListener('click', () => {
    openProductModal();
});

// Add product buttons for specific service types
document.querySelectorAll('.add-product-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const serviceType = btn.getAttribute('data-service');
        openProductModal(serviceType);
    });
});

function openProductModal(serviceType = '') {
    document.getElementById('productModalTitle').textContent = 'Add Service Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    
    if (serviceType) {
        document.getElementById('productServiceType').value = serviceType;
        toggleServiceFields(serviceType);
    }
    
    productModal.classList.remove('hidden');
}

document.getElementById('closeProductModal').addEventListener('click', () => {
    productModal.classList.add('hidden');
});

document.getElementById('cancelProductBtn').addEventListener('click', () => {
    productModal.classList.add('hidden');
});

// Show/hide fields based on service type
document.getElementById('productServiceType').addEventListener('change', (e) => {
    toggleServiceFields(e.target.value);
});

function toggleServiceFields(serviceType) {
    const dataFields = document.getElementById('dataFields');
    
    if (serviceType === 'data') {
        dataFields.classList.remove('hidden');
    } else {
        dataFields.classList.add('hidden');
    }
}

// Product Form Submit
document.getElementById('productForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const productId = document.getElementById('productId').value;
    
    formData.append('action', productId ? 'update_product' : 'add_product');
    
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

// Edit Product
document.querySelectorAll('.edit-product-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const productId = btn.getAttribute('data-id');
        const formData = new FormData();
        formData.append('action', 'get_product');
        formData.append('id', productId);
        
        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                const product = result.data;
                document.getElementById('productModalTitle').textContent = 'Edit Service Product';
                document.getElementById('productId').value = product.id;
                document.getElementById('productServiceType').value = product.service_type;
                document.getElementById('productNetwork').value = product.network_id || '';
                document.getElementById('productName').value = product.name;
                document.getElementById('productPlanCode').value = product.plan_code || '';
                document.getElementById('productAmount').value = product.amount;
                document.getElementById('productSellingPrice').value = product.selling_price;
                document.getElementById('productDiscount').value = product.discount_percentage;
                document.getElementById('productStatus').value = product.status;
                document.getElementById('productDataSize').value = product.data_size || '';
                document.getElementById('productValidity').value = product.validity || '';
                
                toggleServiceFields(product.service_type);
                productModal.classList.remove('hidden');
            } else {
                alert('Error loading product data');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
});

// Delete Product
document.querySelectorAll('.delete-product-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (confirm('Are you sure you want to delete this product?')) {
            const productId = btn.getAttribute('data-id');
            const formData = new FormData();
            formData.append('action', 'delete_product');
            formData.append('id', productId);
            
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

<?php require_once('includes/footer.php'); ?>

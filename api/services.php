<?php
/**
 * Services API Endpoint
 * Provides dynamic service data for frontend AJAX calls
 */
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_services':
            // Get all available services grouped by type
            $stmt = $pdo->query("
                SELECT sp.*, n.display_name as network_name, n.name as network_code, n.code as network_number
                FROM service_products sp 
                LEFT JOIN networks n ON sp.network_id = n.id 
                WHERE sp.status = 'active' 
                ORDER BY sp.service_type, n.name, sp.selling_price ASC
            ");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $services = [];
            foreach ($products as $product) {
                $serviceType = $product['service_type'];
                $networkName = $product['network_name'] ?: 'All Networks';
                
                $services[$serviceType]['name'] = ucfirst($serviceType);
                $services[$serviceType]['networks'][$networkName][] = [
                    'id' => $product['id'],
                    'plan_code' => $product['plan_code'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['selling_price'],
                    'original_price' => $product['amount'],
                    'discount' => $product['discount_percentage'],
                    'data_size' => $product['data_size'],
                    'validity' => $product['validity'],
                    'network_code' => $product['network_code'],
                    'network_number' => $product['network_number']
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $services]);
            break;
            
        case 'get_networks':
            // Get all networks
            $stmt = $pdo->query("SELECT * FROM networks WHERE status = 'active' ORDER BY name ASC");
            $networks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $networks]);
            break;
            
        case 'get_service_by_type':
            $serviceType = $_GET['type'] ?? '';
            
            if (!$serviceType) {
                echo json_encode(['success' => false, 'message' => 'Service type required']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT sp.*, n.display_name as network_name, n.name as network_code, n.code as network_number
                FROM service_products sp 
                LEFT JOIN networks n ON sp.network_id = n.id 
                WHERE sp.service_type = ? AND sp.status = 'active' 
                ORDER BY n.name, sp.selling_price ASC
            ");
            $stmt->execute([$serviceType]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $groupedPlans = [];
            foreach ($products as $product) {
                $networkName = $product['network_name'] ?: 'All Networks';
                $groupedPlans[$networkName][] = [
                    'id' => $product['id'],
                    'plan_code' => $product['plan_code'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['selling_price'],
                    'original_price' => $product['amount'],
                    'discount' => $product['discount_percentage'],
                    'data_size' => $product['data_size'],
                    'validity' => $product['validity'],
                    'network_code' => $product['network_code'],
                    'network_number' => $product['network_number']
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $groupedPlans]);
            break;
            
        case 'detect_network':
            $phoneNumber = $_GET['phone'] ?? '';
            
            if (!$phoneNumber) {
                echo json_encode(['success' => false, 'message' => 'Phone number required']);
                break;
            }
            
            // Simple network detection based on phone number prefixes
            $networkPrefixes = [
                'MTN' => ['0803', '0806', '0703', '0706', '0813', '0810', '0814', '0816', '0903', '0906', '0913', '0916'],
                'GLO' => ['0805', '0807', '0705', '0815', '0811', '0905', '0915'],
                'AIRTEL' => ['0802', '0808', '0708', '0812', '0701', '0902', '0907', '0901', '0904', '0912'],
                '9MOBILE' => ['0809', '0818', '0817', '0909', '0908']
            ];
            
            $detectedNetwork = null;
            $phonePrefix = substr($phoneNumber, 0, 4);
            
            foreach ($networkPrefixes as $network => $prefixes) {
                if (in_array($phonePrefix, $prefixes)) {
                    $detectedNetwork = $network;
                    break;
                }
            }
            
            if ($detectedNetwork) {
                // Get network details from database
                $stmt = $pdo->prepare("SELECT * FROM networks WHERE name = ? AND status = 'active'");
                $stmt->execute([$detectedNetwork]);
                $networkData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'network' => $networkData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Network not detected']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Services API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error occurred']);
}
?>
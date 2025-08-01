<?php
/**
 * Enhanced API Gateway Class
 * Updated to use module-based API providers instead of JSON mappings
 */

require_once(__DIR__ . '/../apis/ApiProviderRegistry.php');

class ModularApiGateway {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get the best available API provider for a service
     */
    public function getProvider($serviceType, $networkId = null) {
        try {
            $sql = "
                SELECT ap.*, apr.service_type, apr.network_id, apr.priority,
                       n.name as network_name, n.code as network_code
                FROM api_provider_routes apr
                JOIN api_providers ap ON apr.api_provider_id = ap.id 
                LEFT JOIN networks n ON apr.network_id = n.id
                WHERE apr.service_type = ? 
                AND apr.status = 'active' 
                AND ap.status = 'active'
                AND (apr.network_id = ? OR apr.network_id IS NULL)
                ORDER BY apr.priority DESC, apr.id ASC 
                LIMIT 1
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$serviceType, $networkId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("API Gateway Provider Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Process airtime purchase using module
     */
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $networkId = $this->getNetworkId($network);
        $providerConfig = $this->getProvider('airtime', $networkId);
        
        if (!$providerConfig) {
            return [
                'success' => false,
                'message' => 'No API provider available for airtime service'
            ];
        }
        
        try {
            $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                'api_key' => $providerConfig['api_key'],
                'secret_key' => $providerConfig['secret_key'],
                'base_url' => $providerConfig['base_url']
            ]);
            
            $result = $provider->purchaseAirtime($phoneNumber, $amount, $network);
            
            // Log transaction
            $this->logTransaction('airtime', $providerConfig['id'], $result, [
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'network' => $network
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Airtime Purchase Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process data purchase using module
     */
    public function purchaseData($phoneNumber, $planCode, $network = null) {
        $networkId = $this->getNetworkId($network);
        $providerConfig = $this->getProvider('data', $networkId);
        
        if (!$providerConfig) {
            return [
                'success' => false,
                'message' => 'No API provider available for data service'
            ];
        }
        
        try {
            $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                'api_key' => $providerConfig['api_key'],
                'secret_key' => $providerConfig['secret_key'],
                'base_url' => $providerConfig['base_url']
            ]);
            
            $result = $provider->purchaseData($phoneNumber, $planCode, $network);
            
            // Log transaction
            $this->logTransaction('data', $providerConfig['id'], $result, [
                'phone_number' => $phoneNumber,
                'plan_code' => $planCode,
                'network' => $network
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Data Purchase Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process cable TV payment using module
     */
    public function payCableTV($smartCardNumber, $productCode, $customerId = null) {
        $providerConfig = $this->getProvider('cable_tv');
        
        if (!$providerConfig) {
            return [
                'success' => false,
                'message' => 'No API provider available for cable TV service'
            ];
        }
        
        try {
            $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                'api_key' => $providerConfig['api_key'],
                'secret_key' => $providerConfig['secret_key'],
                'base_url' => $providerConfig['base_url']
            ]);
            
            $result = $provider->payCableTV($smartCardNumber, $productCode, $customerId);
            
            // Log transaction
            $this->logTransaction('cable_tv', $providerConfig['id'], $result, [
                'smart_card_number' => $smartCardNumber,
                'product_code' => $productCode,
                'customer_id' => $customerId
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Cable TV Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process electricity payment using module
     */
    public function payElectricity($meterNumber, $amount, $discoCode, $meterType = 'prepaid') {
        $providerConfig = $this->getProvider('electricity');
        
        if (!$providerConfig) {
            return [
                'success' => false,
                'message' => 'No API provider available for electricity service'
            ];
        }
        
        try {
            $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                'api_key' => $providerConfig['api_key'],
                'secret_key' => $providerConfig['secret_key'],
                'base_url' => $providerConfig['base_url']
            ]);
            
            $result = $provider->payElectricity($meterNumber, $amount, $discoCode, $meterType);
            
            // Log transaction
            $this->logTransaction('electricity', $providerConfig['id'], $result, [
                'meter_number' => $meterNumber,
                'amount' => $amount,
                'disco_code' => $discoCode,
                'meter_type' => $meterType
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Electricity Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process bulk SMS using module
     */
    public function sendBulkSms($message, $recipients, $senderId = null) {
        $providerConfig = $this->getProvider('bulk_sms');
        
        if (!$providerConfig) {
            return [
                'success' => false,
                'message' => 'No API provider available for bulk SMS service'
            ];
        }
        
        try {
            $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                'api_key' => $providerConfig['api_key'],
                'secret_key' => $providerConfig['secret_key'],
                'base_url' => $providerConfig['base_url']
            ]);
            
            $result = $provider->sendBulkSms($message, $recipients, $senderId);
            
            // Log transaction
            $this->logTransaction('bulk_sms', $providerConfig['id'], $result, [
                'message' => $message,
                'recipients_count' => count($recipients),
                'sender_id' => $senderId
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Bulk SMS Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service temporarily unavailable: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get network ID by name
     */
    private function getNetworkId($networkName) {
        if (!$networkName) return null;
        
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM networks WHERE name = ? OR code = ?");
            $stmt->execute([strtoupper($networkName), strtoupper($networkName)]);
            $result = $stmt->fetch();
            return $result ? $result['id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Log transaction for audit and tracking
     */
    private function logTransaction($serviceType, $providerId, $result, $requestData) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO api_transaction_logs 
                (service_type, provider_id, success, response_message, request_data, response_data, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $serviceType,
                $providerId,
                $result['success'] ? 1 : 0,
                $result['message'] ?? '',
                json_encode($requestData),
                json_encode($result)
            ]);
        } catch (Exception $e) {
            error_log("Transaction Log Error: " . $e->getMessage());
        }
    }
}
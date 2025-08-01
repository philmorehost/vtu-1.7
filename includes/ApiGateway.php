<?php
/**
 * API Gateway Class
 * Handles dynamic routing of API requests to appropriate providers
 */

require_once('../includes/db.php');

class ApiGateway {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get the best available API route for a service
     */
    public function getRoute($serviceType, $networkId = null) {
        try {
            $sql = "
                SELECT ar.*, ap.name as provider_name, ap.base_url, ap.api_key, ap.secret_key, 
                       ap.auth_type, ap.headers, ap.timeout, ap.status as provider_status
                FROM api_routes ar 
                JOIN api_providers ap ON ar.api_provider_id = ap.id 
                WHERE ar.service_type = ? 
                AND ar.status = 'active' 
                AND ap.status = 'active'
                AND (ar.network_id = ? OR ar.network_id IS NULL)
                ORDER BY ar.priority DESC, ar.id ASC 
                LIMIT 1
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$serviceType, $networkId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("API Gateway Route Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get service product details
     */
    public function getServiceProduct($productId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT sp.*, n.name as network_code, n.display_name as network_name 
                FROM service_products sp 
                LEFT JOIN networks n ON sp.network_id = n.id 
                WHERE sp.id = ? AND sp.status = 'active'
            ");
            $stmt->execute([$productId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Service Product Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get service product by plan code
     */
    public function getServiceProductByCode($planCode) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT sp.*, n.name as network_code, n.display_name as network_name 
                FROM service_products sp 
                LEFT JOIN networks n ON sp.network_id = n.id 
                WHERE sp.plan_code = ? AND sp.status = 'active'
            ");
            $stmt->execute([$planCode]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Service Product By Code Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Make API request to provider
     */
    public function makeApiRequest($route, $requestData) {
        $url = $route['base_url'] . $route['endpoint'];
        
        // Parse request mapping
        $requestMapping = json_decode($route['request_mapping'], true) ?: [];
        $apiData = $this->mapRequestData($requestData, $requestMapping);
        
        // Prepare headers
        $headers = ['Content-Type: application/json'];
        
        // Add authentication
        switch ($route['auth_type']) {
            case 'bearer':
                if ($route['api_key']) {
                    $headers[] = 'Authorization: Bearer ' . $route['api_key'];
                }
                break;
            case 'api_key':
                if ($route['api_key']) {
                    $apiData['api_key'] = $route['api_key'];
                }
                break;
            case 'basic':
                if ($route['api_key'] && $route['secret_key']) {
                    $headers[] = 'Authorization: Basic ' . base64_encode($route['api_key'] . ':' . $route['secret_key']);
                }
                break;
        }
        
        // Add custom headers if any
        if ($route['headers']) {
            $customHeaders = json_decode($route['headers'], true);
            if (is_array($customHeaders)) {
                foreach ($customHeaders as $key => $value) {
                    $headers[] = "$key: $value";
                }
            }
        }
        
        // Make the request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $route['method'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $route['timeout'] ?: 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        if (in_array($route['method'], ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $error,
                'http_code' => 0
            ];
        }
        
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $responseData = ['raw_response' => $response];
        }
        
        // Parse response mapping
        $responseMapping = json_decode($route['response_mapping'], true) ?: [];
        $parsedResponse = $this->mapResponseData($responseData, $responseMapping);
        
        return [
            'success' => in_array($httpCode, [200, 201]),
            'http_code' => $httpCode,
            'raw_response' => $response,
            'parsed_response' => $parsedResponse,
            'data' => $responseData
        ];
    }
    
    /**
     * Map request data according to mapping configuration
     */
    private function mapRequestData($requestData, $mapping) {
        $apiData = [];
        
        foreach ($mapping as $apiField => $sourceField) {
            if (is_string($sourceField) && strpos($sourceField, '{') === 0) {
                // Extract variable name from {variable}
                $varName = trim($sourceField, '{}');
                if (isset($requestData[$varName])) {
                    $apiData[$apiField] = $requestData[$varName];
                }
            } else {
                $apiData[$apiField] = $sourceField;
            }
        }
        
        return $apiData;
    }
    
    /**
     * Map response data according to mapping configuration
     */
    private function mapResponseData($responseData, $mapping) {
        $parsedData = [];
        
        foreach ($mapping as $localField => $apiField) {
            if (isset($responseData[$apiField])) {
                $parsedData[$localField] = $responseData[$apiField];
            }
        }
        
        return $parsedData;
    }
    
    /**
     * Record transaction in database
     */
    public function recordTransaction($userId, $type, $description, $amount, $status, $serviceDetails, $source = 'Website', $balanceBefore = 0, $balanceAfter = 0, $batchId = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions 
                (user_id, type, description, amount, status, service_details, source, balance_before, balance_after, batch_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId, $type, $description, $amount, $status, 
                json_encode($serviceDetails), $source, $balanceBefore, $balanceAfter, $batchId
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Transaction Record Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user wallet balance
     */
    public function updateWalletBalance($userId, $amount) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Wallet Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user wallet balance with lock
     */
    public function getUserBalance($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            return $user ? $user['wallet_balance'] : 0;
        } catch (Exception $e) {
            error_log("Get Balance Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Requery transaction status
     */
    public function requeryTransaction($transactionId) {
        try {
            // Get transaction details
            $stmt = $this->pdo->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch();
            
            if (!$transaction) {
                return ['success' => false, 'message' => 'Transaction not found'];
            }
            
            $serviceDetails = json_decode($transaction['service_details'], true);
            $serviceType = strtolower($transaction['type']);
            
            // Get the API route for this service
            $route = $this->getRoute($serviceType, $serviceDetails['network_id'] ?? null);
            
            if (!$route || !$route['requery_endpoint']) {
                return ['success' => false, 'message' => 'Requery not supported for this service'];
            }
            
            // Make requery request
            $requeryData = [
                'transaction_id' => $transactionId,
                'reference' => $serviceDetails['reference'] ?? $transaction['id']
            ];
            
            $response = $this->makeApiRequest($route, $requeryData);
            
            if ($response['success'] && isset($response['parsed_response']['status'])) {
                $newStatus = $response['parsed_response']['status'];
                
                // Update transaction status
                $stmt = $this->pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $transactionId]);
                
                return [
                    'success' => true,
                    'status' => $newStatus,
                    'message' => 'Transaction status updated'
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to requery transaction'];
            
        } catch (Exception $e) {
            error_log("Requery Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Requery error: ' . $e->getMessage()];
        }
    }
}
?>
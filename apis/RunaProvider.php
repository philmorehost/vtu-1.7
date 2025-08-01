<?php
/**
 * Runa API Provider
 * Implementation for Runa gift card services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class RunaProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'runa',
            'display_name' => 'Runa Gift Cards',
            'description' => 'Runa API provider for gift card purchases',
            'website' => 'https://runa.io/',
            'logo' => '/assets/images/providers/runa.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://api.runa.io/v1/';
    }
    
    public function getSupportedServices() {
        return ['gift_card'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'X-API-Secret: ' . $this->secretKey,
            'Content-Type: application/json'
        ];
    }
    
    public function purchaseGiftCard($cardType, $amount, $quantity = 1) {
        $this->validateConfig();
        
        $data = [
            'product_code' => $cardType,
            'value' => $amount,
            'quantity' => $quantity,
            'currency' => 'USD', // Runa typically uses USD
            'external_ref' => uniqid('runa_')
        ];
        
        try {
            $result = $this->makeRequest('orders', $data);
            
            if ($result['http_code'] === 201 || $result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Gift card purchase successful',
                        [
                            'order_id' => $response['order_id'] ?? null,
                            'cards' => $response['cards'] ?? [],
                            'total_amount' => $response['total_amount'] ?? ($amount * $quantity),
                            'currency' => $response['currency'] ?? 'USD',
                            'details' => $response
                        ],
                        $response['order_id'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Gift card purchase failed',
                        $response
                    );
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getAvailableProducts() {
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('products', [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Products retrieved successfully',
                    $response
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getProductDetails($productCode) {
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('products/' . $productCode, [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Product details retrieved successfully',
                    $response
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function checkBalance() {
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('account/balance', [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Balance retrieved successfully',
                    [
                        'balance' => $response['balance'] ?? 'Unknown',
                        'currency' => $response['currency'] ?? 'USD',
                        'available_credit' => $response['available_credit'] ?? 'Unknown'
                    ]
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getOrderStatus($orderId) {
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('orders/' . $orderId, [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Order status retrieved successfully',
                    $response
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function verifyTransaction($transactionId) {
        // Alias for getOrderStatus for compatibility with base class
        return $this->getOrderStatus($transactionId);
    }
    
    public function getOrderHistory($limit = 50, $offset = 0) {
        $this->validateConfig();
        
        $queryParams = http_build_query([
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        try {
            $result = $this->makeRequest('orders?' . $queryParams, [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Order history retrieved successfully',
                    $response
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
}
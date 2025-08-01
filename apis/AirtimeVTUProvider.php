<?php
/**
 * AirtimeVTU API Provider
 * Implementation for AirtimeVTU services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class AirtimeVTUProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'airtimevtu',
            'display_name' => 'AirtimeVTU',
            'description' => 'AirtimeVTU API provider for airtime and data services',
            'website' => 'https://airtimevtu.com/',
            'logo' => '/assets/images/providers/airtimevtu.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://api.airtimevtu.com/v1/';
    }
    
    public function getSupportedServices() {
        return ['airtime', 'data'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'X-API-Secret: ' . $this->secretKey
        ];
    }
    
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();
        
        $networkMap = [
            'MTN' => 'mtn',
            'GLO' => 'glo',
            'AIRTEL' => 'airtel',
            '9MOBILE' => '9mobile'
        ];
        
        $networkCode = $networkMap[strtoupper($network)] ?? 'mtn';
        
        $data = [
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'network' => $networkCode,
            'transaction_id' => uniqid('vtu_')
        ];
        
        try {
            $result = $this->makeRequest('airtime/purchase', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Airtime purchase successful',
                        $response,
                        $response['transaction_id'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Airtime purchase failed',
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
    
    public function purchaseData($phoneNumber, $planCode, $network = null) {
        $this->validateConfig();
        
        $networkMap = [
            'MTN' => 'mtn',
            'GLO' => 'glo',
            'AIRTEL' => 'airtel',
            '9MOBILE' => '9mobile'
        ];
        
        $networkCode = $networkMap[strtoupper($network)] ?? 'mtn';
        
        $data = [
            'phone_number' => $phoneNumber,
            'plan_code' => $planCode,
            'network' => $networkCode,
            'transaction_id' => uniqid('vtu_')
        ];
        
        try {
            $result = $this->makeRequest('data/purchase', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Data purchase successful',
                        $response,
                        $response['transaction_id'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Data purchase failed',
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
    
    public function checkBalance() {
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('account/balance', [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Balance retrieved successfully',
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
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('transaction/verify/' . $transactionId, [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Transaction status retrieved',
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
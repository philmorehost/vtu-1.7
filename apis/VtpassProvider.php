<?php
/**
 * VTPass API Provider
 * Sample implementation for VTPass services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class VtpassProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'vtpass',
            'display_name' => 'VTPass',
            'description' => 'VTPass API provider for bills payment and VTU services',
            'website' => 'https://vtpass.com/',
            'logo' => '/assets/images/providers/vtpass.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://vtpass.com/api/';
    }
    
    public function getSupportedServices() {
        return ['airtime', 'data', 'cable_tv', 'electricity', 'exam'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key'];
    }
    
    protected function getAuthHeaders() {
        return [
            'api-key: ' . $this->apiKey,
            'secret-key: ' . $this->secretKey
        ];
    }
    
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();
        
        $networkMap = [
            'MTN' => 'mtn',
            'GLO' => 'glo',
            'AIRTEL' => 'airtel',
            '9MOBILE' => 'etisalat'
        ];
        
        $serviceId = $networkMap[strtoupper($network)] ?? 'mtn';
        
        $data = [
            'request_id' => uniqid('vtp_'),
            'serviceID' => $serviceId,
            'amount' => $amount,
            'phone' => $phoneNumber
        ];
        
        try {
            $result = $this->makeRequest('pay', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['code']) && $response['code'] === '000') {
                    return $this->formatResponse(
                        true,
                        'Airtime purchase successful',
                        $response,
                        $response['requestId'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['response_description'] ?? 'Airtime purchase failed',
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
        
        $data = [
            'request_id' => uniqid('vtp_'),
            'serviceID' => $planCode, // VTPass uses specific service IDs for data plans
            'billersCode' => $phoneNumber,
            'variation_code' => $planCode,
            'phone' => $phoneNumber
        ];
        
        try {
            $result = $this->makeRequest('pay', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['code']) && $response['code'] === '000') {
                    return $this->formatResponse(
                        true,
                        'Data purchase successful',
                        $response,
                        $response['requestId'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['response_description'] ?? 'Data purchase failed',
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
    
    public function payCableTV($smartCardNumber, $productCode, $customerId = null) {
        $this->validateConfig();
        
        $data = [
            'request_id' => uniqid('vtp_'),
            'serviceID' => $productCode, // e.g., 'dstv', 'gotv', 'startimes'
            'billersCode' => $smartCardNumber,
            'variation_code' => $productCode,
            'amount' => 1, // VTPass will determine amount from variation_code
            'phone' => $customerId ?? $smartCardNumber
        ];
        
        try {
            $result = $this->makeRequest('pay', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['code']) && $response['code'] === '000') {
                    return $this->formatResponse(
                        true,
                        'Cable TV payment successful',
                        $response,
                        $response['requestId'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['response_description'] ?? 'Cable TV payment failed',
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
    
    public function payElectricity($meterNumber, $amount, $discoCode, $meterType = 'prepaid') {
        $this->validateConfig();
        
        $data = [
            'request_id' => uniqid('vtp_'),
            'serviceID' => $discoCode, // e.g., 'eko-electric', 'ikeja-electric'
            'billersCode' => $meterNumber,
            'variation_code' => $meterType,
            'amount' => $amount,
            'phone' => $meterNumber
        ];
        
        try {
            $result = $this->makeRequest('pay', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['code']) && $response['code'] === '000') {
                    return $this->formatResponse(
                        true,
                        'Electricity payment successful',
                        $response,
                        $response['requestId'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['response_description'] ?? 'Electricity payment failed',
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
    
    public function purchaseExamPin($examType, $quantity = 1) {
        $this->validateConfig();
        
        $data = [
            'request_id' => uniqid('vtp_'),
            'serviceID' => $examType, // e.g., 'waec', 'neco'
            'variation_code' => $examType,
            'quantity' => $quantity,
            'phone' => '08000000000' // Required by VTPass
        ];
        
        try {
            $result = $this->makeRequest('pay', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['code']) && $response['code'] === '000') {
                    return $this->formatResponse(
                        true,
                        'Exam pin purchase successful',
                        $response,
                        $response['requestId'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['response_description'] ?? 'Exam pin purchase failed',
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
            $result = $this->makeRequest('balance', [], 'GET');
            
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
        
        $data = [
            'request_id' => $transactionId
        ];
        
        try {
            $result = $this->makeRequest('requery', $data);
            
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
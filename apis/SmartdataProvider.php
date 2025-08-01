<?php
/**
 * Smartdata API Provider
 * Sample implementation for Smartdata VTU services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class SmartdataProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'smartdata',
            'display_name' => 'Smartdata',
            'description' => 'Smartdata API provider for VTU services',
            'website' => 'https://smartdata.com/',
            'logo' => '/assets/images/providers/smartdata.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://smartrecharge.ng/api/v2/';
    }
    
    public function getSupportedServices() {
        return ['airtime', 'data', 'cable_tv', 'electricity', 'exam'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Token ' . $this->apiKey,
            'X-API-Key: ' . $this->secretKey
        ];
    }
    
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();
        
        $data = [
            'network' => strtoupper($network),
            'amount' => $amount,
            'mobile_number' => $phoneNumber,
            'Ported_number' => true
        ];
        
        try {
            $result = $this->makeRequest('airtime/', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['Status']) && $response['Status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Airtime purchase successful',
                        $response,
                        $response['ident'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['api_response'] ?? 'Airtime purchase failed',
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
            'network' => strtoupper($network),
            'mobile_number' => $phoneNumber,
            'plan' => $planCode,
            'Ported_number' => true
        ];
        
        try {
            $result = $this->makeRequest('data/', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['Status']) && $response['Status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Data purchase successful',
                        $response,
                        $response['ident'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['api_response'] ?? 'Data purchase failed',
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
            'cablename' => $productCode, // e.g., 'gotv', 'dstv', 'startimes'
            'cableplan' => $productCode,
            'smart_card_number' => $smartCardNumber
        ];
        
        try {
            $result = $this->makeRequest('cablesub/', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['Status']) && $response['Status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Cable TV payment successful',
                        $response,
                        $response['ident'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['api_response'] ?? 'Cable TV payment failed',
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
            'disco_name' => $discoCode,
            'amount' => $amount,
            'meter_number' => $meterNumber,
            'MeterType' => $meterType
        ];
        
        try {
            $result = $this->makeRequest('billpayment/', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['Status']) && $response['Status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Electricity payment successful',
                        $response,
                        $response['ident'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['api_response'] ?? 'Electricity payment failed',
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
            'exam_name' => $examType, // e.g., 'waec', 'neco', 'jamb'
            'quantity' => $quantity
        ];
        
        try {
            $result = $this->makeRequest('epin/', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['Status']) && $response['Status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Exam pin purchase successful',
                        $response,
                        $response['ident'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['api_response'] ?? 'Exam pin purchase failed',
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
            $result = $this->makeRequest('balance/', [], 'GET');
            
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
            $result = $this->makeRequest('requery/', ['ident' => $transactionId]);
            
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
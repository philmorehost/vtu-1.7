<?php
/**
 * Club Konnect API Provider
 * Sample implementation for ClubKonnect VTU services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class ClubkonnectProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'clubkonnect',
            'display_name' => 'Club Konnect',
            'description' => 'Club Konnect VTU services provider',
            'website' => 'https://www.clubkonnect.com/',
            'logo' => '/assets/images/providers/clubkonnect.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://www.nellobytesystems.com/APIVerify.asp';
    }
    
    public function getSupportedServices() {
        return ['airtime', 'data', 'cable_tv', 'electricity', 'recharge_card', 'betting'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key', 'user_id'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey
        ];
    }
    
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();
        
        $networkMap = [
            'MTN' => '01',
            'GLO' => '02', 
            'AIRTEL' => '03',
            '9MOBILE' => '04'
        ];
        
        $networkCode = $networkMap[$network] ?? '01';
        
        $data = [
            'apikey' => $this->apiKey,
            'service' => 'airtime',
            'network' => $networkCode,
            'phone' => $phoneNumber,
            'amount' => $amount
        ];
        
        try {
            $result = $this->makeRequest('', $data, 'POST');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Airtime purchase successful',
                        $response,
                        $response['transactionid'] ?? null
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

    public function fundBetting($customerId, $amount, $platform) {
        $this->validateConfig();

        $data = [
            'UserID' => $this->config['user_id'],
            'APIKey' => $this->apiKey,
            'BettingCompany' => $platform,
            'CustomerID' => $customerId,
            'Amount' => $amount,
            'RequestID' => uniqid('ck_'),
            'CallBackURL' => '' // Not used in this implementation
        ];

        try {
            $result = $this->makeRequest('APIBettingV1.asp', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['statuscode']) && $response['statuscode'] === '100') {
                    return $this->formatResponse(
                        true,
                        'Betting order received successfully',
                        $response,
                        $response['orderid'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['status'] ?? 'Betting funding failed',
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

    public function verifyBettingCustomer($customerId, $platform) {
        $this->validateConfig();

        $data = [
            'UserID' => $this->config['user_id'],
            'APIKey' => $this->apiKey,
            'BettingCompany' => $platform,
            'CustomerID' => $customerId
        ];

        try {
            $result = $this->makeRequest('APIVerifyBettingV1.asp', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['customer_name']) && !str_contains($response['customer_name'], 'Error')) {
                    return $this->formatResponse(
                        true,
                        'Customer verified successfully',
                        ['Customer_Name' => $response['customer_name']]
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['customer_name'] ?? 'Verification failed'
                    );
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    public function purchaseRechargeCard($network, $amount, $quantity = 1) {
        $this->validateConfig();

        $networkMap = [
            'MTN' => '01',
            'GLO' => '02',
            'AIRTEL' => '04',
            '9MOBILE' => '03'
        ];

        $networkCode = $networkMap[strtoupper($network)] ?? '01';

        $data = [
            'UserID' => $this->config['user_id'],
            'APIKey' => $this->apiKey,
            'MobileNetwork' => $networkCode,
            'Value' => $amount,
            'Quantity' => $quantity,
            'RequestID' => uniqid('ck_'),
            'CallBackURL' => '' // Not used in this implementation
        ];

        try {
            $result = $this->makeRequest('APIEPINV1.asp', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['TXN_EPIN'])) {
                    return $this->formatResponse(
                        true,
                        'Recharge card purchase successful',
                        $response,
                        $response['TXN_EPIN'][0]['transactionid'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['DESCRIPTION'] ?? 'Recharge card purchase failed',
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
            'apikey' => $this->apiKey,
            'service' => 'data',
            'network' => $network,
            'phone' => $phoneNumber,
            'plan' => $planCode
        ];
        
        try {
            $result = $this->makeRequest('', $data, 'POST');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Data purchase successful',
                        $response,
                        $response['transactionid'] ?? null
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
    
    public function payCableTV($smartCardNumber, $productCode, $customerId = null) {
        $this->validateConfig();
        
        $data = [
            'apikey' => $this->apiKey,
            'service' => 'cabletv',
            'smartcard' => $smartCardNumber,
            'package' => $productCode
        ];
        
        try {
            $result = $this->makeRequest('', $data, 'POST');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Cable TV payment successful',
                        $response,
                        $response['transactionid'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Cable TV payment failed',
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
            'apikey' => $this->apiKey,
            'service' => 'electricity',
            'meter' => $meterNumber,
            'amount' => $amount,
            'disco' => $discoCode,
            'type' => $meterType
        ];
        
        try {
            $result = $this->makeRequest('', $data, 'POST');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'successful') {
                    return $this->formatResponse(
                        true,
                        'Electricity payment successful',
                        $response,
                        $response['transactionid'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Electricity payment failed',
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
        
        $data = [
            'apikey' => $this->apiKey,
            'service' => 'balance'
        ];
        
        try {
            $result = $this->makeRequest('', $data, 'POST');
            
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
}
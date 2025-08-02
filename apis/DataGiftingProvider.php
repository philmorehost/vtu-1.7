<?php
/**
 * DataGifting API Provider
 * Implementation for DataGifting bulk SMS services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class DataGiftingProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'datagifting',
            'display_name' => 'DataGifting SMS',
            'description' => 'DataGifting bulk SMS API provider',
            'website' => 'https://datagifting.com/',
            'logo' => '/assets/images/providers/datagifting.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://v5.datagifting.com.ng/web/';
    }
    
    public function getSupportedServices() {
        return ['airtime', 'data', 'cable_tv', 'electricity', 'exam', 'recharge_card', 'betting', 'bulk_sms', 'gift_card'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'user_id'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'X-User-ID: ' . $this->config['user_id']
        ];
    }
    
    public function sendBulkSms($message, $recipients, $senderId = null) {
        $this->validateConfig();

        if (is_array($recipients)) {
            $recipients = implode(',', $recipients);
        }
        
        $data = [
            'api_key' => $this->apiKey,
            'phone_number' => $recipients,
            'sender_id' => $senderId,
            'type' => 'standard_sms', // Assuming standard, could be parameterized later
            'message' => $message
        ];
        
        try {
            $result = $this->makeRequest('api/sms.php', $data, 'POST');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, $response['response_desc'] ?? 'Bulk SMS sent successfully', $response, $response['ref'] ?? null);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Bulk SMS failed', $response);
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
            'user_id' => $this->config['user_id']
        ];
        
        try {
            $result = $this->makeRequest('account/balance', $data, 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Balance retrieved successfully',
                    [
                        'balance' => $response['balance'] ?? 'Unknown',
                        'units' => $response['units'] ?? 'Unknown',
                        'currency' => $response['currency'] ?? 'NGN'
                    ]
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getSenderIds() {
        $this->validateConfig();
        
        $data = [
            'user_id' => $this->config['user_id']
        ];
        
        try {
            $result = $this->makeRequest('sms/senderids', $data, 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Sender IDs retrieved successfully',
                    $response
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getDeliveryReport($messageId) {
        $this->validateConfig();
        
        try {
            $result = $this->makeRequest('sms/report/' . $messageId, [
                'user_id' => $this->config['user_id']
            ], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Delivery report retrieved successfully',
                    $response
                );
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    // --- Placeholder Methods for Newly Supported Services ---

    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'network' => $network, // Assuming the network code is passed in
            'phone_number' => $phoneNumber,
            'amount' => $amount
        ];

        try {
            $result = $this->makeRequest('api/airtime.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['response_desc'] ?? 'Airtime purchase successful',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'Airtime purchase failed',
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

        // The documentation is ambiguous about planCode vs type/quantity.
        // Assuming planCode contains the necessary info, e.g., "sme-data/1gb"
        // This may need refinement based on how products are stored.
        $parts = explode('/', $planCode);
        $type = $parts[0] ?? 'sme-data';
        $quantity = $parts[1] ?? '1gb';

        $data = [
            'api_key' => $this->apiKey,
            'network' => $network,
            'phone_number' => $phoneNumber,
            'type' => $type,
            'quantity' => $quantity
        ];

        try {
            $result = $this->makeRequest('api/data.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['response_desc'] ?? 'Data purchase successful',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'Data purchase failed',
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

    public function payCableTV($smartCardNumber, $productCode, $network = null) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'type' => $network, // Assuming network holds the cable type e.g., 'dstv'
            'iuc_number' => $smartCardNumber,
            'package' => $productCode
        ];

        try {
            $result = $this->makeRequest('api/cable.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, $response['response_desc'] ?? 'Cable TV payment successful', $response, $response['ref'] ?? null);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Cable TV payment failed', $response);
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    public function verifyCable($smartCardNumber, $network) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'type' => $network,
            'iuc_number' => $smartCardNumber
        ];

        try {
            $result = $this->makeRequest('api/verify-cable.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, 'Verification successful', ['customer_name' => $response['desc']]);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Verification failed', $response);
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
            'api_key' => $this->apiKey,
            'type' => $meterType,
            'meter_number' => $meterNumber,
            'provider' => $discoCode,
            'amount' => $amount
        ];

        try {
            $result = $this->makeRequest('api/electric.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, $response['response_desc'] ?? 'Electricity payment successful', $response, $response['ref'] ?? null);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Electricity payment failed', $response);
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    public function verifyMeter($meterNumber, $discoCode, $meterType = 'prepaid') {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'type' => $meterType,
            'meter_number' => $meterNumber,
            'provider' => $discoCode
        ];

        try {
            $result = $this->makeRequest('api/verify-electric.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, 'Verification successful', ['customer_name' => $response['desc']]);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Verification failed', $response);
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
            'api_key' => $this->apiKey,
            'type' => $examType,
            'quantity' => $quantity
        ];

        try {
            $result = $this->makeRequest('api/exam.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, $response['response_desc'] ?? 'Exam pin purchase successful', $response, $response['ref'] ?? null);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Exam pin purchase failed', $response);
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    public function purchaseRechargeCard($network, $amount, $quantity) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'network' => $network,
            'qty_number' => $quantity,
            'type' => 'rechargecard', // As per documentation
            'quantity' => $amount // 'quantity' in docs seems to mean the card value
        ];

        try {
            $result = $this->makeRequest('api/card.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(true, $response['response_desc'] ?? 'Recharge card purchase successful', $response, $response['ref'] ?? null);
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Recharge card purchase failed', $response);
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    public function fundBetting($customerId, $amount, $platform) {
        return $this->formatResponse(false, 'Betting account funding is not yet implemented for this provider.');
    }

    public function purchaseGiftCard($cardType, $amount, $quantity) {
        return $this->formatResponse(false, 'Gift Card purchase is not yet implemented for this provider.');
    }
}
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
        return 'https://api.datagifting.com/v1/';
    }
    
    public function getSupportedServices() {
        return ['bulk_sms'];
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
        
        // Prepare recipients array
        $recipientList = [];
        if (is_string($recipients)) {
            $recipientList = explode(',', $recipients);
        } elseif (is_array($recipients)) {
            $recipientList = $recipients;
        } else {
            return $this->formatResponse(false, 'Invalid recipients format');
        }
        
        // Clean and validate phone numbers
        $cleanRecipients = [];
        foreach ($recipientList as $recipient) {
            $clean = preg_replace('/[^0-9+]/', '', trim($recipient));
            if (strlen($clean) >= 10) {
                $cleanRecipients[] = $clean;
            }
        }
        
        if (empty($cleanRecipients)) {
            return $this->formatResponse(false, 'No valid recipients found');
        }
        
        $data = [
            'sender' => $senderId ?: 'DataGifting',
            'to' => implode(',', $cleanRecipients),
            'message' => $message,
            'type' => 'text',
            'user_id' => $this->config['user_id']
        ];
        
        try {
            $result = $this->makeRequest('sms/send', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Bulk SMS sent successfully',
                        [
                            'message_count' => count($cleanRecipients),
                            'sent_to' => count($cleanRecipients),
                            'failed' => 0,
                            'units_used' => $response['units_used'] ?? count($cleanRecipients),
                            'details' => $response
                        ],
                        $response['message_id'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'SMS sending failed',
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
}
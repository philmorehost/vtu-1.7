<?php
/**
 * KudiSMS API Provider
 * Implementation for KudiSMS bulk SMS services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class KudiSMSProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'kudisms',
            'display_name' => 'KudiSMS',
            'description' => 'KudiSMS bulk SMS API provider',
            'website' => 'https://kudisms.com/',
            'logo' => '/assets/images/providers/kudisms.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://kudisms.net/api/';
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
            'Content-Type: application/json'
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
            'api_token' => $this->apiKey,
            'from' => $senderId ?: 'KudiSMS',
            'message' => $message,
            'to' => $cleanRecipients,
            'user_id' => $this->config['user_id']
        ];
        
        try {
            $result = $this->makeRequest('', $data);
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                
                if (isset($response['status']) && $response['status'] === 'OK') {
                    return $this->formatResponse(
                        true,
                        'Bulk SMS sent successfully',
                        [
                            'message_count' => count($cleanRecipients),
                            'sent_to' => count($cleanRecipients),
                            'failed' => 0,
                            'cost' => $response['cost'] ?? 'Unknown',
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
            'api_token' => $this->apiKey,
            'user_id' => $this->config['user_id']
        ];
        
        try {
            $result = $this->makeRequest('balance', $data, 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Balance retrieved successfully',
                    [
                        'balance' => $response['balance'] ?? 'Unknown',
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
            'api_token' => $this->apiKey,
            'user_id' => $this->config['user_id']
        ];
        
        try {
            $result = $this->makeRequest('senderids', $data, 'GET');
            
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
    
    public function getMessageStatus($messageId) {
        $this->validateConfig();
        
        $data = [
            'api_token' => $this->apiKey,
            'message_id' => $messageId
        ];
        
        try {
            $result = $this->makeRequest('status', $data, 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Message status retrieved successfully',
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
<?php
/**
 * Generic Bulk SMS Provider
 * Sample implementation for bulk SMS services
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class BulkSmsProvider extends BaseApiProvider {
    
    public function getProviderInfo() {
        return [
            'name' => 'bulksms',
            'display_name' => 'Bulk SMS Provider',
            'description' => 'Generic bulk SMS API provider',
            'website' => 'https://bulksms.example.com/',
            'logo' => '/assets/images/providers/bulksms.png'
        ];
    }
    
    protected function getDefaultBaseUrl() {
        return 'https://api.bulksms.com/v1/';
    }
    
    public function getSupportedServices() {
        return ['bulk_sms'];
    }
    
    public function getRequiredConfig() {
        return ['api_key', 'secret_key'];
    }
    
    protected function getAuthHeaders() {
        return [
            'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->secretKey)
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
                $cleanRecipients[] = ['msisdn' => $clean];
            }
        }
        
        if (empty($cleanRecipients)) {
            return $this->formatResponse(false, 'No valid recipients found');
        }
        
        $data = [
            'from' => $senderId ?: 'VTU Platform',
            'to' => $cleanRecipients,
            'body' => $message,
            'encoding' => 'TEXT'
        ];
        
        try {
            $result = $this->makeRequest('messages', $data);
            
            if ($result['http_code'] === 201) {
                $response = $result['response'];
                
                if (isset($response[0]['id'])) {
                    return $this->formatResponse(
                        true,
                        'Bulk SMS sent successfully',
                        [
                            'message_count' => count($cleanRecipients),
                            'sent_to' => count($cleanRecipients),
                            'failed' => 0,
                            'details' => $response
                        ],
                        $response[0]['id']
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        'SMS sending failed',
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
            $result = $this->makeRequest('profile', [], 'GET');
            
            if ($result['http_code'] === 200) {
                $response = $result['response'];
                return $this->formatResponse(
                    true,
                    'Balance retrieved successfully',
                    [
                        'balance' => $response['credits']['balance'] ?? 'Unknown',
                        'currency' => $response['credits']['currency'] ?? 'USD'
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
        
        try {
            $result = $this->makeRequest('senderids', [], 'GET');
            
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
}
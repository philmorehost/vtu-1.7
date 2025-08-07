<?php
/**
 * Base API Provider Class
 * Abstract class that all API provider modules must extend
 */

abstract class BaseApiProvider {
    protected $config;
    protected $apiKey;
    protected $secretKey;
    protected $baseUrl;
    protected $timeout;
    
    public function __construct($config = []) {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? null;
        $this->secretKey = $config['secret_key'] ?? null;
        $this->baseUrl = $config['base_url'] ?? $this->getDefaultBaseUrl();
        $this->timeout = $config['timeout'] ?? 30;
    }
    
    /**
     * Get provider information
     */
    abstract public function getProviderInfo();
    
    /**
     * Get default base URL for this provider
     */
    abstract protected function getDefaultBaseUrl();
    
    /**
     * Get supported services for this provider
     */
    abstract public function getSupportedServices();
    
    /**
     * Get required configuration fields
     */
    abstract public function getRequiredConfig();
    
    /**
     * Purchase airtime
     */
    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        throw new Exception('Airtime not supported by this provider');
    }
    
    /**
     * Purchase data
     */
    public function purchaseData($phoneNumber, $planCode, $network = null) {
        throw new Exception('Data not supported by this provider');
    }
    
    /**
     * Send bulk SMS
     */
    public function sendBulkSms($message, $recipients, $senderId = null) {
        throw new Exception('Bulk SMS not supported by this provider');
    }
    
    /**
     * Pay for cable TV
     */
    public function payCableTV($smartCardNumber, $productCode, $customerId = null) {
        throw new Exception('Cable TV not supported by this provider');
    }
    
    /**
     * Pay electricity bill
     */
    public function payElectricity($meterNumber, $amount, $discoCode, $meterType = 'prepaid') {
        throw new Exception('Electricity not supported by this provider');
    }
    
    /**
     * Purchase exam pin
     */
    public function purchaseExamPin($examType, $quantity = 1) {
        throw new Exception('Exam pin not supported by this provider');
    }
    
    /**
     * Fund betting account
     */
    public function fundBetting($customerId, $amount, $platform) {
        throw new Exception('Betting not supported by this provider');
    }
    
    /**
     * Purchase recharge card
     */
    public function purchaseRechargeCard($network, $amount, $quantity = 1) {
        throw new Exception('Recharge card not supported by this provider');
    }
    
    /**
     * Purchase gift card
     */
    public function purchaseGiftCard($cardType, $amount, $quantity = 1) {
        throw new Exception('Gift card not supported by this provider');
    }
    
    /**
     * Check account balance
     */
    public function checkBalance() {
        throw new Exception('Balance check not supported by this provider');
    }

    /**
     * Verify a smart card number for Cable TV
     */
    public function verifySmartCard($smartCardNumber, $providerCode) {
        throw new Exception('Smart card verification not supported by this provider');
    }

    /**
     * Verify a meter number for Electricity
     */
    public function verifyMeterNumber($meterNumber, $discoCode, $meterType = 'prepaid') {
        throw new Exception('Meter number verification not supported by this provider');
    }
    
    /**
     * Verify transaction status
     */
    public function verifyTransaction($transactionId) {
        throw new Exception('Transaction verification not supported by this provider');
    }
    
    /**
     * Make HTTP request
     */
    protected function makeRequest($endpoint, $data = [], $method = 'POST', $headers = []) {
        $url = $this->baseUrl . $endpoint;
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $headers = array_merge($defaultHeaders, $headers, $this->getAuthHeaders());
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response);
        }
        
        return [
            'http_code' => $httpCode,
            'response' => $decodedResponse,
            'raw_response' => $response
        ];
    }
    
    /**
     * Get authentication headers for requests
     */
    protected function getAuthHeaders() {
        return [];
    }
    
    /**
     * Standardize response format
     */
    protected function formatResponse($success, $message, $data = null, $transactionId = null) {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'transaction_id' => $transactionId,
            'provider' => $this->getProviderInfo()['name']
        ];
    }
    
    /**
     * Validate required configuration
     */
    public function validateConfig() {
        $required = $this->getRequiredConfig();
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($this->config[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required configuration: ' . implode(', ', $missing));
        }
        
        return true;
    }
}
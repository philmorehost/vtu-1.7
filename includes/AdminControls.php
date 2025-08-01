<?php
/**
 * Admin Controls Utilities
 * Functions for checking transaction limits and blocked identifiers
 */

class AdminControls {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check if an identifier is blocked
     * @param string $identifierType - Type of identifier (phone, meter_number, etc.)
     * @param string $identifierValue - The actual identifier value
     * @return array - ['blocked' => bool, 'reason' => string]
     */
    public function isIdentifierBlocked($identifierType, $identifierValue) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT reason FROM blocked_identifiers 
                WHERE identifier_type = ? AND identifier_value = ?
            ");
            $stmt->execute([$identifierType, $identifierValue]);
            $result = $stmt->fetch();
            
            if ($result) {
                return [
                    'blocked' => true,
                    'reason' => $result['reason'] ?: 'This identifier has been blocked by an administrator.'
                ];
            }
            
            return ['blocked' => false, 'reason' => ''];
        } catch (PDOException $e) {
            // In case of error, allow transaction to proceed
            return ['blocked' => false, 'reason' => ''];
        }
    }
    
    /**
     * Check if transaction limit would be exceeded
     * @param string $identifierType - Type of identifier
     * @param string $identifierValue - The actual identifier value
     * @return array - ['exceeded' => bool, 'limit' => int, 'current' => int, 'period' => string]
     */
    public function checkTransactionLimit($identifierType, $identifierValue) {
        try {
            // Get the current limit settings
            $stmt = $this->pdo->prepare("
                SELECT max_transactions, period_type 
                FROM transaction_limits 
                WHERE identifier_type = ? 
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([$identifierType]);
            $limit = $stmt->fetch();
            
            if (!$limit) {
                // No limit set, allow transaction
                return ['exceeded' => false, 'limit' => 0, 'current' => 0, 'period' => 'unlimited'];
            }
            
            // Calculate the time period to check
            $periodStart = $this->getPeriodStart($limit['period_type']);
            
            // Count transactions for this identifier in the current period
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as transaction_count 
                FROM transactions 
                WHERE JSON_EXTRACT(service_details, '$.$field') = ? 
                AND created_at >= ? 
                AND status = 'Completed'
            ");
            
            // Build the JSON path based on identifier type
            $jsonField = $this->getServiceDetailsField($identifierType);
            $query = str_replace('$field', $jsonField, $stmt->queryString);
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$identifierValue, $periodStart]);
            $result = $stmt->fetch();
            
            $currentCount = $result['transaction_count'];
            $exceeded = $currentCount >= $limit['max_transactions'];
            
            return [
                'exceeded' => $exceeded,
                'limit' => $limit['max_transactions'],
                'current' => $currentCount,
                'period' => $limit['period_type']
            ];
            
        } catch (PDOException $e) {
            // In case of error, allow transaction to proceed
            return ['exceeded' => false, 'limit' => 0, 'current' => 0, 'period' => 'unlimited'];
        }
    }
    
    /**
     * Get the start datetime for the current period
     */
    private function getPeriodStart($periodType) {
        switch ($periodType) {
            case 'daily':
                return date('Y-m-d 00:00:00');
            case 'weekly':
                return date('Y-m-d 00:00:00', strtotime('monday this week'));
            case 'monthly':
                return date('Y-m-01 00:00:00');
            default:
                return date('Y-m-d 00:00:00');
        }
    }
    
    /**
     * Get the JSON field name in service_details for each identifier type
     */
    private function getServiceDetailsField($identifierType) {
        switch ($identifierType) {
            case 'phone':
                return 'phoneNumber';
            case 'meter_number':
                return 'meterNumber';
            case 'smartcard_number':
                return 'smartCardNumber';
            case 'betting_id':
                return 'bettingId';
            default:
                return 'identifier';
        }
    }
    
    /**
     * Check if SMS sender ID is approved
     * @param int $userId - User ID
     * @param string $senderId - Sender ID to check
     * @return array - ['approved' => bool, 'message' => string]
     */
    public function isSenderIdApproved($userId, $senderId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT status, review_notes 
                FROM sms_sender_ids 
                WHERE user_id = ? AND sender_id = ?
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId, $senderId]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return [
                    'approved' => false,
                    'message' => 'Sender ID not registered. Please register and wait for approval.'
                ];
            }
            
            switch ($result['status']) {
                case 'approved':
                    return ['approved' => true, 'message' => ''];
                case 'disapproved':
                    $reason = $result['review_notes'] ?: 'Sender ID was disapproved.';
                    return ['approved' => false, 'message' => "Sender ID disapproved: {$reason}"];
                case 'pending':
                default:
                    return ['approved' => false, 'message' => 'Sender ID is pending approval.'];
            }
            
        } catch (PDOException $e) {
            return ['approved' => false, 'message' => 'Error checking sender ID status.'];
        }
    }
    
    /**
     * Check if SMS contains blocked keywords
     * @param string $message - SMS message content
     * @return array - ['blocked' => bool, 'keyword' => string]
     */
    public function checkBlockedKeywords($message) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT identifier_value 
                FROM blocked_identifiers 
                WHERE identifier_type = 'sms_keyword'
            ");
            $stmt->execute();
            $keywords = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $message = strtolower($message);
            foreach ($keywords as $keyword) {
                if (strpos($message, strtolower($keyword)) !== false) {
                    return [
                        'blocked' => true,
                        'keyword' => $keyword
                    ];
                }
            }
            
            return ['blocked' => false, 'keyword' => ''];
            
        } catch (PDOException $e) {
            return ['blocked' => false, 'keyword' => ''];
        }
    }
}
?>
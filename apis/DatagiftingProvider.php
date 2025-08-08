<?php
/**
 * DATAGIFTING API Provider
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class DatagiftingProvider extends BaseApiProvider {

    public function getProviderInfo() {
        return [
            'name' => 'datagifting',
            'display_name' => 'DATAGIFTING',
            'description' => 'DATAGIFTING API provider for VTU services',
            'website' => 'https://v5.datagifting.com.ng/',
            'logo' => '/assets/images/providers/datagifting.png'
        ];
    }

    protected function getDefaultBaseUrl() {
        return 'https://v5.datagifting.com.ng/web/api/';
    }

    public function getSupportedServices() {
        return ['airtime', 'data', 'cable_tv', 'electricity', 'exam', 'recharge_card', 'bulksms'];
    }

    public function getRequiredConfig() {
        return ['api_key'];
    }

    public function purchaseAirtime($phoneNumber, $amount, $network = null) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'network' => $network,
            'phone_number' => $phoneNumber,
            'amount' => $amount
        ];

        try {
            $result = $this->makeRequest('airtime.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'Airtime purchase successful',
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

        $dataType = $this->config['data_type'] ?? 'sme-data';

        $data = [
            'api_key' => $this->apiKey,
            'network' => $network,
            'phone_number' => $phoneNumber,
            'type' => $dataType,
            'quantity' => $planCode
        ];

        try {
            $result = $this->makeRequest('data.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'Data purchase successful',
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

    public function sendBulkSms($message, $recipients, $senderId = null) {
        $this->validateConfig();

        $type = $this->config['sms_type'] ?? 'standard_sms';
        $schedule = $this->config['schedule'] ?? null;

        $data = [
            'api_key' => $this->apiKey,
            'phone_number' => is_array($recipients) ? implode(',', $recipients) : $recipients,
            'sender_id' => $senderId,
            'type' => $type,
            'message' => $message,
            'date' => $schedule
        ];

        try {
            $result = $this->makeRequest('sms.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'SMS sent successfully',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'SMS sending failed',
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

        $providerCode = $this->config['provider_code'] ?? null;

        $data = [
            'api_key' => $this->apiKey,
            'type' => $providerCode,
            'iuc_number' => $smartCardNumber,
            'package' => $productCode
        ];

        try {
            $result = $this->makeRequest('cable.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'Cable TV payment successful',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'Cable TV payment failed',
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
            'api_key' => $this->apiKey,
            'type' => $meterType,
            'meter_number' => $meterNumber,
            'provider' => $discoCode,
            'amount' => $amount
        ];

        try {
            $result = $this->makeRequest('electric.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'Electricity payment successful',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'Electricity payment failed',
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
            'api_key' => $this->apiKey,
            'type' => $examType,
            'quantity' => $quantity
        ];

        try {
            $result = $this->makeRequest('exam.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'Exam pin purchase successful',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'Exam pin purchase failed',
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

    public function purchaseRechargeCard($network, $amount, $quantity = 1) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'network' => $network,
            'qty_number' => $quantity,
            'type' => 'rechargecard',
            'quantity' => $amount
        ];

        try {
            $result = $this->makeRequest('card.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        $response['desc'] ?? 'Card purchase successful',
                        $response,
                        $response['ref'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['desc'] ?? 'Card purchase failed',
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

    public function verifyCableTV($smartCardNumber, $providerCode, $productCode) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'type' => $providerCode,
            'iuc_number' => $smartCardNumber,
            'package' => $productCode
        ];

        try {
            $result = $this->makeRequest('verify-cable.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Verification successful',
                        ['Customer_Name' => $response['desc']]
                    );
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Verification failed');
                }
            } else {
                return $this->formatResponse(false, 'HTTP Error: ' . $result['http_code']);
            }
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    public function verifyElectricity($meterNumber, $discoCode, $meterType) {
        $this->validateConfig();

        $data = [
            'api_key' => $this->apiKey,
            'type' => $meterType,
            'meter_number' => $meterNumber,
            'provider' => $discoCode
        ];

        try {
            $result = $this->makeRequest('verify-electric.php', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === 'success') {
                    return $this->formatResponse(
                        true,
                        'Verification successful',
                        ['Customer_Name' => $response['desc']]
                    );
                } else {
                    return $this->formatResponse(false, $response['desc'] ?? 'Verification failed');
                }
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
            'api_key' => $this->apiKey,
            'reference' => $transactionId
        ];

        try {
            $result = $this->makeRequest('requery.php', $data, 'POST');

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
?>

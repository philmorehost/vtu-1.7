<?php
/**
 * NaijaResultPins API Provider
 */

require_once(__DIR__ . '/BaseApiProvider.php');

class NaijaresultpinsProvider extends BaseApiProvider {

    public function getProviderInfo() {
        return [
            'name' => 'naijaresultpins',
            'display_name' => 'NaijaResultPins',
            'description' => 'NaijaResultPins API provider for exam cards',
            'website' => 'https://www.naijaresultpins.com/',
            'logo' => '/assets/images/providers/naijaresultpins.png'
        ];
    }

    protected function getDefaultBaseUrl() {
        return 'https://www.naijaresultpins.com/api/v1/';
    }

    public function getSupportedServices() {
        return ['exam'];
    }

    public function getRequiredConfig() {
        return ['api_key'];
    }

    protected function getAuthHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];
    }

    public function purchaseExamPin($examType, $quantity = 1) {
        $this->validateConfig();

        $data = [
            'card_type_id' => $examType,
            'quantity' => $quantity
        ];

        try {
            $result = $this->makeRequest('exam-card/buy', $data, 'POST');

            if ($result['http_code'] === 200) {
                $response = $result['response'];

                if (isset($response['status']) && $response['status'] === true) {
                    return $this->formatResponse(
                        true,
                        $response['message'] ?? 'Exam pin purchase successful',
                        $response,
                        $response['reference'] ?? null
                    );
                } else {
                    return $this->formatResponse(
                        false,
                        $response['message'] ?? 'Exam pin purchase failed',
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

    public function getExamCards() {
        $this->validateConfig();

        try {
            $result = $this->makeRequest('', [], 'GET');

            if ($result['http_code'] === 200) {
                return $this->formatResponse(
                    true,
                    'Exam cards retrieved successfully',
                    $result['response']
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

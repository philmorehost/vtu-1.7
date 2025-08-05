<?php
// cron/requery_all_pending.php
// This script is designed to be run by a cron job to automatically requery pending transactions.

// Set a long execution time, as this might take a while if there are many transactions.
set_time_limit(300); // 5 minutes

// Change to the project's root directory
chdir(dirname(__DIR__));

require_once('includes/db.php');
require_once('includes/ModularApiGateway.php');

echo "Cron Job Started: " . date('Y-m-d H:i:s') . "\n";

try {
    // 1. Fetch all pending transactions
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE status = 'Pending'");
    $stmt->execute();
    $pendingTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pendingTransactions)) {
        echo "No pending transactions to requery.\n";
        exit();
    }

    echo "Found " . count($pendingTransactions) . " pending transactions to check.\n";

    foreach ($pendingTransactions as $transaction) {
        $transactionId = $transaction['id'];
        $userId = $transaction['user_id'];

        echo "  - Checking Transaction ID: {$transactionId} for User ID: {$userId}\n";

        try {
            // 2. Identify the provider
            $serviceDetails = json_decode($transaction['service_details'], true);
            $network = $serviceDetails['network'] ?? null;

            $gateway = new ModularApiGateway($pdo, $userId);
            $networkId = $gateway->getNetworkId($network);
            $providerConfig = $gateway->getProvider($transaction['type'], $networkId);

            if (!$providerConfig) {
                echo "    - Error: Could not determine provider for transaction {$transactionId}.\n";
                continue;
            }

            // 3. Call verifyTransaction
            $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
                'api_key' => $providerConfig['api_key'],
                'secret_key' => $providerConfig['secret_key'],
                'base_url' => $providerConfig['base_url'],
                'user_id' => $userId
            ]);

            if ((new ReflectionMethod(get_class($provider), 'verifyTransaction'))->getDeclaringClass()->getName() === BaseApiProvider::class) {
                echo "    - Skipping: Transaction verification is not implemented for provider '{$providerConfig['provider_module']}'.\n";
                continue;
            }

            $verificationResult = $provider->verifyTransaction($transactionId);

            // 4. Update status
            $newStatus = $transaction['status'];
            if ($verificationResult['success']) {
                $providerStatus = $verificationResult['data']['status'] ?? 'pending';
                if (strtolower($providerStatus) === 'successful' || strtolower($providerStatus) === 'completed') {
                    $newStatus = 'Completed';
                } elseif (strtolower($providerStatus) === 'failed' || strtolower($providerStatus) === 'reversed') {
                    $newStatus = 'Failed';
                }
            }

            if ($newStatus !== $transaction['status']) {
                $updateStmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
                $updateStmt->execute([$newStatus, $transactionId]);
                echo "    - Status updated to: {$newStatus}\n";

                if ($newStatus === 'Failed') {
                    $refundAmount = -1 * $transaction['amount'];
                    $refundStmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                    $refundStmt->execute([$refundAmount, $userId]);
                    echo "    - Refunded ₦{$refundAmount} to user {$userId}.\n";
                }
            } else {
                echo "    - Status remains Pending.\n";
            }

        } catch (Exception $e) {
            echo "    - Error processing transaction {$transactionId}: " . $e->getMessage() . "\n";
            error_log("Cron Requery Error for TXN ID {$transactionId}: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    echo "A fatal error occurred: " . $e->getMessage() . "\n";
    error_log("Fatal Cron Requery Error: " . $e->getMessage());
}

echo "Cron Job Finished: " . date('Y-m-d H:i:s') . "\n";
?>

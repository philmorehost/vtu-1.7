<?php
/**
 * Requery API Endpoint
 * Verifies the status of a pending transaction with the API provider.
 */

header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/ModularApiGateway.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$transactionId = $_POST['transaction_id'] ?? $_GET['transaction_id'] ?? null;

if (!$transactionId) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required.']);
    exit();
}

try {
    // 1. Fetch the transaction details from your database
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, $userId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found or you do not have permission to view it.']);
        exit();
    }

    // Optional: Only allow requery for pending transactions
    if ($transaction['status'] !== 'Pending') {
         echo json_encode(['success' => true, 'message' => 'Transaction status is already final.', 'status' => $transaction['status']]);
         exit();
    }

    // 2. Identify the provider used for this transaction
    // This requires that you log which provider was used. Let's assume you have a `provider` column in your `transactions` table.
    // If not, you might need to add it or find another way to determine the provider.
    // For now, we'll have to guess or use the current default provider.

    $serviceDetails = json_decode($transaction['service_details'], true);
    $network = $serviceDetails['network'] ?? null;

    // We need to instantiate the gateway with the user_id to use providers that require it
    $gateway = new ModularApiGateway($pdo, $userId);
    $networkId = $gateway->getNetworkId($network); // Using a helper method from the gateway

    // We need to find the provider that was used for the original transaction.
    // This information should ideally be stored with the transaction.
    // For this example, we'll get the current default provider for the service.
    $providerConfig = $gateway->getProvider($transaction['type'], $networkId);

    if (!$providerConfig) {
        echo json_encode(['success' => false, 'message' => 'Could not determine the API provider for this transaction.']);
        exit();
    }

    // 3. Call the verifyTransaction method on the provider
    $provider = ApiProviderRegistry::getProvider($providerConfig['provider_module'], [
        'api_key' => $providerConfig['api_key'],
        'secret_key' => $providerConfig['secret_key'],
        'base_url' => $providerConfig['base_url'],
        'user_id' => $userId
    ]);

    $verificationResult = $provider->verifyTransaction($transactionId);

    // 4. Update your database with the new status
    $newStatus = $transaction['status']; // Default to old status
    if ($verificationResult['success']) {
        // This part is highly dependent on the provider's response format.
        // You need to map their status to your application's status (e.g., 'Completed', 'Failed').
        // Let's assume a simple case for now.
        $providerStatus = $verificationResult['data']['status'] ?? 'pending'; // Example path
        if (strtolower($providerStatus) === 'successful' || strtolower($providerStatus) === 'completed') {
            $newStatus = 'Completed';
        } elseif (strtolower($providerStatus) === 'failed' || strtolower($providerStatus) === 'reversed') {
            $newStatus = 'Failed';
        }
    }

    if ($newStatus !== $transaction['status']) {
        $updateStmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $transactionId]);

        // Handle refund if the transaction failed
        if ($newStatus === 'Failed') {
            // Use the original transaction amount (it's negative)
            $refundAmount = -1 * $transaction['amount'];
            $refundStmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $refundStmt->execute([$refundAmount, $userId]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Transaction status updated.', 'status' => $newStatus]);

} catch (Exception $e) {
    error_log("Requery Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during requery: ' . $e->getMessage()]);
}
?>

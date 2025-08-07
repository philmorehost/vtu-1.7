<?php
// cron.php - A script to be run by a cron job to handle automated tasks.

// Increase execution time for this script.
set_time_limit(300); // 5 minutes

require_once(__DIR__ . '/includes/db.php');
require_once(__DIR__ . '/includes/ModularApiGateway.php');

echo "Cron Job Started: " . date('Y-m-d H:i:s') . "\n";

// --- Task: Re-query Pending Transactions ---
echo "Task: Re-querying pending transactions...\n";

try {
    $gateway = new ModularApiGateway($pdo);

    // Fetch all transactions that are still pending
    $stmt = $pdo->query("SELECT id, type, description FROM transactions WHERE status = 'Pending'");
    $pendingTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pendingTransactions)) {
        echo "No pending transactions found.\n";
    } else {
        echo "Found " . count($pendingTransactions) . " pending transaction(s).\n";

        foreach ($pendingTransactions as $transaction) {
            echo "  - Re-querying transaction #" . $transaction['id'] . " ({$transaction['description']})... ";

            $result = $gateway->requeryTransaction($transaction['id']);

            if ($result['success']) {
                $newStatus = $result['new_status'] ?? 'N/A';
                echo "Success! New status: " . $newStatus . "\n";
            } else {
                echo "Failed. Reason: " . ($result['message'] ?? 'Unknown error') . "\n";
            }
            // Small delay to avoid hammering APIs
            sleep(1);
        }
    }

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    error_log("Cron Job Error: " . $e->getMessage());
}

echo "Cron Job Finished: " . date('Y-m-d H:i:s') . "\n";
?>

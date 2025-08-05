<?php
// This script is for testing the new exam card API functionality.
// It should be run after running setup_test_provider.php.

require_once('includes/db.php');
require_once('includes/ModularApiGateway.php');
require_once('apis/NaijaResultPinsProvider.php');

// Mock the session for testing purposes
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1; // Assuming user with ID 1 exists

echo "<h1>Testing Exam API</h1>";

$gateway = new ModularApiGateway($pdo);

echo "<h2>1. Testing getAvailableExamCards()</h2>";
$cards = $gateway->getAvailableExamCards();
echo "<pre>" . print_r($cards, true) . "</pre>";

echo "<h2>2. Testing getAccountInfo()</h2>";
$accountInfo = $gateway->getAccountInfo();
echo "<pre>" . print_r($accountInfo, true) . "</pre>";

echo "<h2>3. Testing purchaseExamCard()</h2>";
// We need a valid card_type_id to test this.
// We will try to get one from the getAvailableExamCards() response.
if (!empty($cards['data'][0]['card_type_id'])) {
    $cardTypeId = $cards['data'][0]['card_type_id'];
    echo "<p>Attempting to purchase card with type ID: {$cardTypeId}</p>";
    $purchase = $gateway->purchaseExamCard($cardTypeId, 1);
    echo "<pre>" . print_r($purchase, true) . "</pre>";
} else {
    echo "<p>Could not get a card_type_id, skipping purchase test.</p>";
}

echo "<h2>4. Testing Cron Job Logic</h2>";
echo "<p>To test the cron job, run this command in your terminal:</p>";
echo "<pre>php cron/requery_all_pending.php</pre>";
echo "<p>Check the output for any errors. The script should skip the NaijaResultPins provider because the verifyTransaction method is not fully implemented.</p>";

?>

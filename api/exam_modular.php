<?php
header('Content-Type: application/json');
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('../includes/ModularApiGateway.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$modularGateway = new ModularApiGateway($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if ($action === 'get_cards') {
        $response = $modularGateway->getAvailableExamCards();
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardTypeId = $_POST['card_type_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if (!$cardTypeId || !$quantity || !is_numeric($quantity) || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Card type ID and quantity are required.']);
        exit();
    }

    $response = $modularGateway->purchaseExamCard($cardTypeId, $quantity);
    echo json_encode($response);
    exit();
}
?>

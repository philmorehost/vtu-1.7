<?php
// notification_actions.php
require_once('../includes/session_config.php');
// Assuming auth_check.php handles session_start() and auth checks for non-AJAX requests
// For AJAX, we'll do a specific check below.
require_once('auth_check.php'); 
require_once('../includes/db.php');

// --- ALWAYS Check Authentication for AJAX Requests ---
// Adjust the session variable check based on your login system
// This should match the check in your other protected AJAX handlers
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // --- CRITICAL: For AJAX, DO NOT redirect. Return JSON error. ---
    header('Content-Type: application/json');
    // Use appropriate HTTP status code, e.g., 401 Unauthorized
    http_response_code(401); 
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required. Please log in.'
        // You could also include a flag like 'redirect' => '/admin/login.php'
        // and handle it in JavaScript if you prefer.
    ]);
    exit(); // Stop script execution
}
// --- End of Authentication Check ---

$action = $_GET['action'] ?? '';

if ($action === 'post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Check for POST action ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // For non-AJAX POST, redirect is okay
        header('Location: notifications.php?error=csrf');
        exit();
    }
    // --- End CSRF Check ---
    
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';
    $user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;

    if ($title && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $title, $message]);
            header('Location: notifications.php?success=posted');
            exit();
        } catch (PDOException $e) {
            // Log the error for debugging (don't expose to user)
            error_log("Post Notification Error (PDO): " . $e->getMessage());
            header('Location: notifications.php?error=db_error');
            exit();
        }
    } else {
        header('Location: notifications.php?error=missing_fields');
        exit();
    }
} elseif ($action === 'delete' && isset($_GET['id'])) {
    // This is for the individual delete link, which uses GET and redirects
    $notification_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$notification_id]);
        header('Location: notifications.php?success=deleted');
        exit();
    } catch (PDOException $e) {
        // Log the error for debugging (don't expose to user)
        error_log("Delete Notification Error (PDO): " . $e->getMessage());
        header('Location: notifications.php?error=db_error');
        exit();
    }
} elseif ($action === 'delete_multiple' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Modified 'delete_multiple' logic for AJAX ---
    
    // --- CSRF Check for delete_multiple action ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Return JSON error for AJAX request instead of redirect
        header('Content-Type: application/json');
        // Use appropriate HTTP status code, e.g., 403 Forbidden
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token. Please refresh the page.']);
        exit();
    }
    // --- End CSRF Check ---

    $notification_ids = $_POST['notification_ids'] ?? [];
    if (empty($notification_ids)) {
        // Return JSON for empty selection
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No notifications selected.']);
        exit();
    }
    
    // --- Use try...catch for robust error handling ---
    try {
        // Prepare placeholders for the IN clause to prevent SQL injection
        $placeholders = implode(',', array_fill(0, count($notification_ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id IN ($placeholders)");
        $stmt->execute($notification_ids);
        
        // Return JSON success for AJAX request
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Selected notifications deleted successfully.']);
        exit();
        
    } catch (PDOException $e) {
        // Log the actual error for debugging (don't expose to user)
        error_log("Delete Multiple Notifications Error (PDO): " . $e->getMessage());
        // Return JSON error for database failure
        header('Content-Type: application/json');
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'A database error occurred while deleting notifications.']);
        exit();
    } catch (Error $e) { // Catch fatal errors/PHP errors if possible
         error_log("Delete Multiple Notifications Error (PHP): " . $e->getMessage());
         header('Content-Type: application/json');
         http_response_code(500);
         echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
         exit();
    } catch (Exception $e) {
         error_log("Delete Multiple Notifications Error (Exception): " . $e->getMessage());
         header('Content-Type: application/json');
         http_response_code(500);
         echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
         exit();
    }
    // --- End of Modified 'delete_multiple' logic ---
} else {
    // Default redirect for unknown actions or incorrect methods
    header('Location: notifications.php');
    exit();
}
?>
<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'error' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    $response['error'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['item_id']) || !isset($_POST['action'])) {
    $response['error'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

$item_id = intval($_POST['item_id']);
$action = $_POST['action'];

global $conn;

if ($action === 'approve') {
    try {
        $stmt = $conn->prepare("UPDATE purchase_items SET item_approval_status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$item_id]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Purchase item approved successfully.';
        } else {
            $response['error'] = 'No changes made or item not found.';
        }
    } catch (PDOException $e) {
        error_log("Database error (ajax_update_item_approval_status.php): " . $e->getMessage());
        $response['error'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Invalid action specified.';
}

echo json_encode($response);
?>



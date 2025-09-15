<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Check if user is logged in and authorized
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'accountsadmin' && $_SESSION['user_type'] !== 'superadmin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$purchase_id = $_POST['purchase_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$purchase_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate status
$valid_statuses = ['pending', 'sent_for_approval', 'approved'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

global $conn;

try {
    // Update approval status in purchase_main table
    $sql = "UPDATE purchase_main SET approval_status = :status WHERE id = :purchase_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':purchase_id', $purchase_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $message = '';
        switch ($status) {
            case 'sent_for_approval':
                $message = 'Purchase sent for approval successfully';
                break;
            case 'approved':
                $message = 'Purchase approved successfully';
                break;
            case 'pending':
                $message = 'Purchase status updated to pending';
                break;
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update approval status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
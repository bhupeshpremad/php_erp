<?php
session_start();
include '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $admin_id = $_POST['admin_id'];
    
    if (empty($admin_id)) {
        echo json_encode(['success' => false, 'message' => 'Admin ID is required']);
        exit;
    }
    
    // Update admin status to rejected (deactivated)
    $stmt = $conn->prepare("UPDATE admin_users SET status = 'rejected', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$admin_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Admin deactivated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
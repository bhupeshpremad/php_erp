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
    $department = $_POST['department'];
    
    if (empty($admin_id) || empty($department)) {
        echo json_encode(['success' => false, 'message' => 'Admin ID and department are required']);
        exit;
    }
    
    // Update admin status and assign department
    $stmt = $conn->prepare("UPDATE admin_users SET status = 'approved', department = ?, approved_by = 1, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$department, $admin_id]);
    
    echo json_encode(['success' => true, 'message' => 'Admin approved successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
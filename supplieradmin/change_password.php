<?php
session_start();
include '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['supplier_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $supplier_id = $_SESSION['supplier_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Get current password from database
    $stmt = $conn->prepare("SELECT password FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch();
    
    if (!$supplier) {
        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
        exit;
    }
    
    // Verify current password
    if (!password_verify($current_password, $supplier['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Validate new password
    if (strlen($new_password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $new_password)) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number and special character']);
        exit;
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE suppliers SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $supplier_id]);
    
    echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
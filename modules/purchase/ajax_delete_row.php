<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if user is superadmin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only superadmin can delete rows.']);
    exit;
}

$row_id = $_POST['row_id'] ?? null;

if (!$row_id) {
    echo json_encode(['success' => false, 'error' => 'Row ID is required']);
    exit;
}

global $conn;

try {
    $stmt = $conn->prepare("DELETE FROM purchase_items WHERE id = ?");
    $stmt->execute([$row_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Row deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Row not found or already deleted']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
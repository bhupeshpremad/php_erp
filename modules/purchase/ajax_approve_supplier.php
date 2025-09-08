<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$supplier_id = $_POST['supplier_id'] ?? null;
$purchase_id = $_POST['purchase_id'] ?? null;

if (!$supplier_id || !$purchase_id) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters.']);
    exit;
}

global $conn;

try {
    // Update all items for this supplier in this purchase to approved status
    $stmt = $conn->prepare("UPDATE purchase_items SET approval_status = 'approved', updated_at = NOW() WHERE purchase_main_id = ? AND supplier_name = ?");
    $stmt->execute([$purchase_id, $supplier_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Supplier approved successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'No items found for this supplier or already approved.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
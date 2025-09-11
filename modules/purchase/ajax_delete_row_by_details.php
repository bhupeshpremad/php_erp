<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Allow accountsadmin and superadmin to delete
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'superadmin' && $_SESSION['user_type'] !== 'accountsadmin')) {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only admin can delete rows.']);
    exit;
}

$supplier_name = $_POST['supplier_name'] ?? null;
$product_name = $_POST['product_name'] ?? null;
$job_card_number = $_POST['job_card_number'] ?? null;
$jci_number = $_POST['jci_number'] ?? null;
$row_id = isset($_POST['row_id']) ? intval($_POST['row_id']) : null;

if (!$supplier_name || !$product_name || !$job_card_number || !$jci_number) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

global $conn;

try {
    // Get purchase_main_id first
    $stmt_main = $conn->prepare("SELECT id FROM purchase_main WHERE jci_number = ?");
    $stmt_main->execute([$jci_number]);
    $purchase_main = $stmt_main->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase_main) {
        echo json_encode(['success' => false, 'error' => 'Purchase record not found']);
        exit;
    }
    
    // First check if row exists
    $check_stmt = $conn->prepare("SELECT id FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_name = ? AND job_card_number = ?");
    $check_stmt->execute([$purchase_main['id'], $supplier_name, $product_name, $job_card_number]);
    $existing_row = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_row) {
        echo json_encode(['success' => false, 'error' => 'Row not found', 'debug' => [
            'purchase_main_id' => $purchase_main['id'],
            'supplier_name' => $supplier_name,
            'product_name' => $product_name,
            'job_card_number' => $job_card_number
        ]]);
        exit;
    }
    
    // Delete the row
    $stmt = $conn->prepare("DELETE FROM purchase_items WHERE id = ?");
    $stmt->execute([$existing_row['id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Row deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete row']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
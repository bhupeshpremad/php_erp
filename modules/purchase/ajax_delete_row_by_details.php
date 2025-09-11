<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if user is superadmin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    echo json_encode(['success' => false, 'error' => 'Access denied. Only superadmin can delete rows.']);
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
    
    // Delete specific row, include row_id if provided for precise match
    if ($row_id !== null) {
        $stmt = $conn->prepare("DELETE FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_name = ? AND job_card_number = ? AND row_id = ?");
        $stmt->execute([$purchase_main['id'], $supplier_name, $product_name, $job_card_number, $row_id]);
    } else {
        $stmt = $conn->prepare("DELETE FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_name = ? AND job_card_number = ?");
        $stmt->execute([$purchase_main['id'], $supplier_name, $product_name, $job_card_number]);
    }
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Row deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Row not found or already deleted']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
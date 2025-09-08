<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$po_id = $_POST['po_id'] ?? null;
$sell_order_id = $_POST['sell_order_id'] ?? null;

if (!$po_id && !$sell_order_id) {
    echo json_encode(['success' => false, 'message' => 'PO ID or Sell Order ID is required']);
    exit;
}

// If sell_order_id is provided, get po_id from it
if ($sell_order_id && !$po_id) {
    $stmt_po = $conn->prepare("SELECT po_id FROM sell_order WHERE id = ?");
    $stmt_po->execute([$sell_order_id]);
    $po_result = $stmt_po->fetch(PDO::FETCH_ASSOC);
    if ($po_result) {
        $po_id = $po_result['po_id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'PO not found for sell order']);
        exit;
    }
}

try {
    // Corrected: Fetch product details directly from po_items table
    $stmt = $conn->prepare("
        SELECT id, product_code, product_name, quantity
        FROM po_items
        WHERE po_id = ?
    ");
    $stmt->execute([$po_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add item_code field for compatibility
    foreach ($products as &$product) {
        $product['item_code'] = $product['product_code'];
    }
    
    echo json_encode(['success' => true, 'products' => $products]);
    exit;
} catch (Exception $e) {
    error_log("Error fetching PO products: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred while fetching PO products.']);
    exit;
}
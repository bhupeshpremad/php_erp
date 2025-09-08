<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$sell_order_id = $_POST['sell_order_id'] ?? null;

if (!$sell_order_id) {
    echo json_encode(['success' => false, 'message' => 'Sell Order ID required']);
    exit;
}

try {
    global $conn;
    
    // Debug: Log the sell_order_id
    error_log("Fetching PO for sell_order_id: " . $sell_order_id);
    
    $stmt = $conn->prepare("SELECT po.id, po.po_number, po.delivery_date FROM sell_order so JOIN po_main po ON so.po_id = po.id WHERE so.id = ?");
    $stmt->execute([$sell_order_id]);
    $po_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: Log the result
    error_log("PO Data found: " . json_encode($po_data));
    
    if ($po_data) {
        echo json_encode(['success' => true, 'po_data' => $po_data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'PO not found for sell_order_id: ' . $sell_order_id]);
    }
    
} catch (Exception $e) {
    error_log("PO Data fetch error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
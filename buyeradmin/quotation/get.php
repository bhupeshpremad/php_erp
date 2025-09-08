<?php
session_start();
include '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['buyer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit;
}

try {
    // Get quotation
    $stmt = $conn->prepare("SELECT * FROM buyer_quotations WHERE id = ? AND buyer_id = ?");
    $stmt->execute([$id, $_SESSION['buyer_id']]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quotation) {
        echo json_encode(['success' => false, 'message' => 'Quotation not found']);
        exit;
    }
    
    // Get products
    $stmt = $conn->prepare("SELECT *, product_image_name AS image_path FROM quotation_products WHERE quotation_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'quotation_date' => $quotation['quotation_date'],
            'customer_name' => $quotation['customer_name'],
            'customer_email' => $quotation['customer_email'],
            'customer_phone' => $quotation['customer_phone'],
            'delivery_term' => $quotation['delivery_term'],
            'terms_of_delivery' => $quotation['terms_of_delivery'],
            'products' => $products
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

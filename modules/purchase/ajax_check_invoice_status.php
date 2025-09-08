<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_POST['jci_number'])) {
    echo json_encode(['error' => 'JCI number is required']);
    exit;
}

// Check if this is a specific item check or general JCI check
if (!isset($_POST['supplier_name']) || !isset($_POST['product_type']) || !isset($_POST['product_name'])) {
    // General JCI check - return all items with invoice status
    $jci_number = $_POST['jci_number'];
    
    try {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT pi.supplier_name, pi.product_type, pi.product_name, pi.invoice_number, pi.invoice_image, pi.builty_image 
            FROM purchase_items pi
            INNER JOIN purchase_main pm ON pi.purchase_main_id = pm.id
            WHERE pm.jci_number = ? 
            AND (pi.invoice_number IS NOT NULL AND pi.invoice_number != '')
            AND (pi.invoice_image IS NOT NULL AND pi.invoice_image != '')
        ");
        
        $stmt->execute([$jci_number]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'items_with_invoice' => $results
        ]);
        exit;
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$jci_number = $_POST['jci_number'];
$supplier_name = $_POST['supplier_name'];
$product_type = $_POST['product_type'];
$product_name = $_POST['product_name'];

try {
    global $conn;
    
    // Check if invoice has been uploaded for this specific BOM item
    $stmt = $conn->prepare("
        SELECT pi.invoice_number, pi.invoice_image, pi.builty_image 
        FROM purchase_items pi
        INNER JOIN purchase_main pm ON pi.purchase_main_id = pm.id
        WHERE pm.jci_number = ? 
        AND pi.supplier_name = ? 
        AND pi.product_type = ? 
        AND pi.product_name = ?
        AND (pi.invoice_number IS NOT NULL AND pi.invoice_number != '')
        AND (pi.invoice_image IS NOT NULL AND pi.invoice_image != '')
        LIMIT 1
    ");
    
    $stmt->execute([$jci_number, $supplier_name, $product_type, $product_name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'has_invoice' => true,
            'invoice_number' => $result['invoice_number'],
            'invoice_file' => $result['invoice_image'],
            'billty_file' => $result['builty_image']
        ]);
    } else {
        echo json_encode(['has_invoice' => false]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

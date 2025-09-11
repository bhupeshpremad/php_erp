<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$jci_number = $_POST['jci_number'] ?? null;

if (!$jci_number) {
    echo json_encode(['success' => false, 'error' => 'JCI number is required']);
    exit;
}

global $conn;

try {
    // Check if purchase exists for this JCI
    $stmt_main = $conn->prepare("SELECT * FROM purchase_main WHERE jci_number = ?");
    $stmt_main->execute([$jci_number]);
    $purchase_main = $stmt_main->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase_main) {
        echo json_encode(['success' => true, 'has_purchase' => false]);
        exit;
    }
    
    // Fetch purchase items with proper data structure
    $stmt_items = $conn->prepare("
        SELECT 
            pi.*,
            TRIM(COALESCE(pi.supplier_name, '')) as supplier_name, 
            TRIM(COALESCE(pi.product_type, '')) as product_type, 
            TRIM(COALESCE(pi.product_name, '')) as product_name, 
            TRIM(COALESCE(pi.job_card_number, '')) as job_card_number,
            COALESCE(pi.length_ft, 0) as length_ft,
            COALESCE(pi.width_ft, 0) as width_ft,
            COALESCE(pi.thickness_inch, 0) as thickness_inch,
            COALESCE(pi.row_id, 0) as row_id,
            COALESCE(pi.assigned_quantity, 0) as assigned_quantity,
            COALESCE(pi.price, 0) as price,
            COALESCE(pi.total, 0) as total
        FROM purchase_items pi
        WHERE pi.purchase_main_id = ?
        ORDER BY pi.row_id ASC, pi.id ASC
    ");
    $stmt_items->execute([$purchase_main['id']]);
    $purchase_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    
    // Clean supplier names to remove any whitespace issues
    foreach ($purchase_items as &$item) {
        $item['supplier_name'] = trim($item['supplier_name'] ?? '');
        $item['product_type'] = trim($item['product_type'] ?? '');
        $item['product_name'] = trim($item['product_name'] ?? '');
        $item['job_card_number'] = trim($item['job_card_number'] ?? '');
    }
    
    echo json_encode([
        'success' => true,
        'has_purchase' => true,
        'purchase_main' => $purchase_main,
        'purchase_items' => $purchase_items
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
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
    
    // Fetch purchase items with proper data cleaning
    $stmt_items = $conn->prepare("SELECT *, TRIM(supplier_name) as supplier_name, TRIM(product_type) as product_type, TRIM(product_name) as product_name, TRIM(job_card_number) as job_card_number FROM purchase_items WHERE purchase_main_id = ?");
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
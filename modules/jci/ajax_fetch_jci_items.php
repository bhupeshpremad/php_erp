<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$jci_id = (int)($_POST['jci_id'] ?? 0); // This jci_id will now correspond to a single JOB-YEAR-JCN-X entry in jci_main

if ($jci_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Job Card ID provided.']);
    exit;
}

try {
    // Fetch JCI items with product images from PO
    $stmt = $conn->prepare("SELECT
                                ji.job_card_number,
                                ji.po_product_id,
                                ji.product_name,
                                ji.item_code,
                                ji.original_po_quantity,
                                ji.quantity,
                                ji.labour_cost,
                                ji.total_amount,
                                ji.delivery_date,
                                ji.job_card_date,
                                ji.job_card_type,
                                ji.contracture_name,
                                pi.product_image
                            FROM jci_items ji
                            LEFT JOIN po_items pi ON ji.po_product_id = pi.id
                            WHERE ji.jci_id = ?");
    $stmt->execute([$jci_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug and process product image paths
    foreach ($items as &$item) {
        // Debug log
        error_log("JCI Item Debug - po_product_id: " . $item['po_product_id'] . ", product_image: " . ($item['product_image'] ?? 'NULL'));
        
        if (!empty($item['product_image'])) {
            // Convert relative path to full URL - images are stored in modules/po/uploads/
            $item['product_image'] = BASE_URL . 'modules/po/uploads/' . $item['product_image'];
        } else {
            $item['product_image'] = null;
        }
    }
    
    // Add debug info to response
    $response = ['success' => true, 'items' => $items];
    
    // Debug: Check if po_items table has images
    $debug_stmt = $conn->prepare("SELECT id, product_name, product_image FROM po_items WHERE product_image IS NOT NULL AND product_image != '' LIMIT 5");
    $debug_stmt->execute();
    $debug_images = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['debug_po_images'] = $debug_images;

    echo json_encode($response);
    exit;
} catch (Exception $e) {
    error_log("Error fetching JCI items: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred while fetching items. Please try again later.']);
    exit;
}
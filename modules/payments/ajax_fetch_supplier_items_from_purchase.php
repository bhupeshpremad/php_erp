<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'supplier_items' => []];

if (isset($_GET['po_id'])) {
    $po_id = intval($_GET['po_id']);
    try {
        // Fetch purchase_main id(s) for the given PO id
        $stmt_pm = $conn->prepare("SELECT id FROM purchase_main WHERE po_main_id = ?");
        $stmt_pm->execute([$po_id]);
        $purchase_ids = $stmt_pm->fetchAll(PDO::FETCH_COLUMN);

        $supplier_items = [];

        foreach ($purchase_ids as $purchase_id) {
            // Fetch all APPROVED items from purchase_items table for the current purchase_main_id
            $stmt_approved_items = $conn->prepare("SELECT product_name AS item_name, assigned_quantity AS quantity, price, total AS amount FROM purchase_items WHERE purchase_main_id = ? AND item_approval_status = 'approved'");
            $stmt_approved_items->execute([$purchase_id]);
            $approved_items = $stmt_approved_items->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($approved_items)) {
                $supplier_items[] = [
                    'purchase_id' => $purchase_id,
                    'items' => $approved_items
                ];
            }
        }

        $response['success'] = true;
        $response['supplier_items'] = $supplier_items;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching supplier items from purchase: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'PO ID not provided.';
}

echo json_encode($response);
?>

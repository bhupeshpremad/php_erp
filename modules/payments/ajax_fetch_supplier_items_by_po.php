<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'supplier_items' => []];

if (isset($_GET['po_id'])) {
    $po_id = intval($_GET['po_id']);
    try {
        // Fetch supplier items related to the given PO id
        // Assuming purchase_main_id in supplier related tables links to PO
        // Fetch supplier groups and their items for the PO

        // Fetch suppliers for the PO
        $stmt_suppliers = $conn->prepare("SELECT id, supplier_name, invoice_number, invoice_amount FROM purchase_suppliers WHERE po_id = ?");
        $stmt_suppliers->execute([$po_id]);
        $suppliers = $stmt_suppliers->fetchAll(PDO::FETCH_ASSOC);

        $supplier_items = [];

        foreach ($suppliers as $supplier) {
            // Fetch items for each supplier
            $stmt_items = $conn->prepare("SELECT item_name, quantity, price, amount FROM purchase_supplier_items WHERE supplier_id = ?");
            $stmt_items->execute([$supplier['id']]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            $supplier_items[] = [
                'supplier_name' => $supplier['supplier_name'],
                'invoice_number' => $supplier['invoice_number'],
                'invoice_amount' => $supplier['invoice_amount'],
                'items' => $items
            ];
        }

        $response['success'] = true;
        $response['supplier_items'] = $supplier_items;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching supplier items: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'PO ID not provided.';
}

echo json_encode($response);
?>

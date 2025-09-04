<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'po_amt' => null, 'soa' => null, 'son_number' => null];

if (isset($_GET['po_id'])) {
    $po_id = intval($_GET['po_id']);
    try {
        // Sum total_amount from po_items for the given po_id as PO AMT
        $stmt_po_amt = $conn->prepare("SELECT SUM(total_amount) as po_amt FROM po_items WHERE po_id = ?");
        $stmt_po_amt->execute([$po_id]);
        $po_amt_result = $stmt_po_amt->fetch(PDO::FETCH_ASSOC);
        $po_amt = $po_amt_result['po_amt'] ?? 0;

        // For SOA, try to get from sell_order table or set to 0 if not found
        $stmt_soa = $conn->prepare("SELECT sell_order_number FROM sell_order WHERE po_id = ? LIMIT 1");
        $stmt_soa->execute([$po_id]);
        $soa_result = $stmt_soa->fetch(PDO::FETCH_ASSOC);
        $soa = $soa_result ? 0 : 0; // No amount column found, so set 0

        // Fetch SON (Sale Order Number) from sell_order table
        $stmt_son = $conn->prepare("SELECT sell_order_number FROM sell_order WHERE po_id = ? LIMIT 1");
        $stmt_son->execute([$po_id]);
        $son_result = $stmt_son->fetch(PDO::FETCH_ASSOC);
        $son_number = $son_result['sell_order_number'] ?? null;

        $response['success'] = true;
        $response['po_amt'] = $po_amt;
        $response['soa'] = $po_amt; // Set SOA same as PO AMT as per user request
        $response['son_number'] = $son_number;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching PO amounts: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'PO number ID not provided.';
}

echo json_encode($response);
?>

<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'vendors' => []];

try {
    // Fetch distinct vendor payment records from payments table
    $stmt = $conn->prepare("
        SELECT 
            MIN(p.id) as id,
            p.jci_number,
            pm.po_number,
            pm.sell_order_number as son_number,
            GROUP_CONCAT(DISTINCT p.supplier_name SEPARATOR ', ') as supplier_name,
            'Paid' as payment_status
        FROM payments p
        LEFT JOIN po_main pm ON p.po_id = pm.id
        GROUP BY p.jci_number, pm.po_number, pm.sell_order_number
        ORDER BY p.jci_number ASC
    ");
    $stmt->execute();
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each vendor, fetch payment id for edit link
    foreach ($vendors as &$vendor) {
        $vendor['payment_id'] = $vendor['id'];
        // Ensure keys exist and are not null
        $vendor['jci_number'] = $vendor['jci_number'] ?? '';
        $vendor['po_number'] = $vendor['po_number'] ?? '';
        $vendor['son_number'] = $vendor['son_number'] ?? '';
        $vendor['supplier_name'] = $vendor['supplier_name'] ?? '';
    }

    $response['success'] = true;
    $response['vendors'] = $vendors;
} catch (Exception $e) {
    $response['message'] = 'Error fetching vendors with payment status: ' . $e->getMessage();
}

echo json_encode($response);
?>

<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'payments' => []];

try {
    // Fetch all payments with their details, grouped by JCI number
    $stmt = $conn->prepare("
        SELECT
            jm.jci_number,
            pm.po_number,
            pm.sell_order_number,
            MIN(p.id) AS payment_id, -- Representative payment ID for actions
            COALESCE(SUM(pi.total), 0) AS total_purchase_items_amount,
            COALESCE(SUM(ji.total_amount), 0) AS total_jci_items_amount,
            COALESCE(SUM(pd.total_with_gst), 0) AS total_paid_amount,
            COUNT(DISTINCT pi.id) AS total_approved_purchase_items,
            COUNT(DISTINCT CASE WHEN pd.id IS NOT NULL THEN pi.id END) AS paid_purchase_items
        FROM
            jci_main jm
        LEFT JOIN
            po_main pm ON jm.po_id = pm.id
        LEFT JOIN
            purchase_main pmain ON jm.jci_number = pmain.jci_number
        LEFT JOIN
            purchase_items pi ON pmain.id = pi.purchase_main_id AND pi.item_approval_status = 'approved'
        LEFT JOIN
            jci_items ji ON jm.id = ji.jci_id
        LEFT JOIN
            payments p ON jm.jci_number = p.jci_number -- Join to payments table for this JCI
        LEFT JOIN
            payment_details pd ON p.id = pd.payment_id AND pd.payment_category = 'Supplier'
        GROUP BY
            jm.jci_number, pm.po_number, pm.sell_order_number
        ORDER BY
            jm.jci_number DESC"
    );
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['payments'] = $payments;
    
} catch (Exception $e) {
    $response['message'] = 'Error fetching payments: ' . $e->getMessage();
}

echo json_encode($response);
?>
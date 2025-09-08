<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'payments' => []];

try {
    // Fetch all payments with their details - similar to php_erp2 structure
    $stmt = $conn->prepare(
        "SELECT 
            p.id,
            p.jci_number,
            p.po_number,
            p.sell_order_number,
            p.created_at,
            COUNT(pd.id) AS total_suppliers,
            COUNT(CASE WHEN pd.payment_date IS NOT NULL AND pd.cheque_number IS NOT NULL THEN pd.id END) AS paid_suppliers,
            COUNT(CASE WHEN pd.payment_date IS NOT NULL AND pd.cheque_number IS NOT NULL THEN pd.id END) AS completed_payments,
            COALESCE(SUM(pd.ptm_amount), 0) AS total_amount,
            COALESCE(SUM(CASE WHEN pd.payment_category = 'Supplier' AND pd.payment_date IS NOT NULL AND pd.cheque_number IS NOT NULL THEN pd.ptm_amount ELSE 0 END), 0) AS supplier_paid_amount
        FROM payments p
        LEFT JOIN payment_details pd ON p.id = pd.payment_id
        GROUP BY p.id, p.jci_number, p.po_number, p.sell_order_number, p.created_at
        ORDER BY p.created_at DESC"
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
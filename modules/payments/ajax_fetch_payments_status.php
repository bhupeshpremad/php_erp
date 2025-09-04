<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'payments' => []];

try {
    // Select data from jci_main, which has the payment status flag
    $stmt = $conn->prepare("
        SELECT 
            jm.jci_number, 
            jm.payment_completed,
            p.id AS payment_id,
            p.po_number,
            p.sell_order_number
        FROM jci_main jm
        LEFT JOIN payments p ON jm.jci_number = p.jci_number
        ORDER BY jm.id DESC
    ");
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data for the front-end
    foreach ($payments as &$payment) {
        $payment['payment_status'] = ($payment['payment_completed'] == 1) ? 'Completed' : 'Pending';
        // Remove payment_completed as we have a status string now
        unset($payment['payment_completed']); 
    }

    $response['success'] = true;
    $response['payments'] = $payments;

} catch (Exception $e) {
    $response['message'] = 'Error fetching payments: ' . $e->getMessage();
}

echo json_encode($response);
?>
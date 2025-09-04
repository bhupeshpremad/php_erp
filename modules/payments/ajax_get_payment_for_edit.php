<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'data' => null];

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];
    
    try {
        // Get payment main data
        $stmt_payment = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt_payment->execute([$payment_id]);
        $payment_data = $stmt_payment->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment_data) {
            throw new Exception('Payment not found');
        }
        
        // Get ALL payment details for this JCI number, not just for this payment_id
        $stmt_details = $conn->prepare("
            SELECT 
                pd.*,
                CASE 
                    WHEN pd.payment_category = 'Supplier' THEN (
                        SELECT pi.supplier_name 
                        FROM purchase_items pi 
                        JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
                        WHERE pm.jci_number = ? AND pi.invoice_number = pd.cheque_number 
                        LIMIT 1
                    )
                    WHEN pd.payment_category = 'Job Card' THEN (
                        SELECT ji.contracture_name 
                        FROM jci_items ji 
                        JOIN jci_main jm ON ji.jci_id = jm.id 
                        WHERE jm.jci_number = ? 
                        LIMIT 1
                    )
                    ELSE 'Unknown'
                END as entity_name
            FROM payment_details pd 
            JOIN payments p ON pd.payment_id = p.id
            WHERE p.jci_number = ?
        ");
        $stmt_details->execute([$payment_data['jci_number'], $payment_data['jci_number'], $payment_data['jci_number']]);
        $payment_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['data'] = [
            'payment' => $payment_data,
            'payment_details' => $payment_details
        ];
        
    } catch (Exception $e) {
        $response['message'] = 'Error fetching payment data: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Payment ID not provided.';
}

echo json_encode($response);
?>
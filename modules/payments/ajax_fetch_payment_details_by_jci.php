<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'payment_details' => []];

if (isset($_GET['jci_number'])) {
    $jci_number = $_GET['jci_number'];
    
    try {
        // Get payment details for the given JCI number
        $stmt_details = $conn->prepare("
            SELECT 
                pd.id, pd.payment_id, pd.jc_number, pd.payment_type, pd.cheque_number, pd.pd_acc_number, pd.ptm_amount, 
                pd.gst_percent, pd.gst_amount, pd.total_with_gst, pd.payment_invoice_date, pd.payment_date, pd.payment_category, 
                pd.amount, pd.created_at, pd.updated_at,
                pi.invoice_number, pi.invoice_image, pi.builty_image,
                CASE 
                    WHEN pd.payment_category = 'Supplier' THEN pi.supplier_name 
                    WHEN pd.payment_category = 'Job Card' THEN ji.contracture_name 
                    ELSE 'Unknown'
                END as entity_name
            FROM payment_details pd 
            JOIN payments p ON pd.payment_id = p.id
            LEFT JOIN purchase_items pi ON (
                p.jci_number = ? AND pd.payment_category = 'Supplier' AND pi.invoice_number = pd.cheque_number
            )
            LEFT JOIN jci_items ji ON (
                p.jci_number = ? AND pd.payment_category = 'Job Card' AND ji.job_card_number = pd.jc_number
            )
            WHERE p.jci_number = ?
            ORDER BY pd.created_at ASC
        ");
        $stmt_details->execute([$jci_number, $jci_number, $jci_number]);
        $payment_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['payment_details'] = $payment_details;
        
    } catch (Exception $e) {
        $response['message'] = 'Error fetching payment data: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'JCI Number not provided.';
}

echo json_encode($response);
?>

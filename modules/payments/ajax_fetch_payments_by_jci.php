<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'payments' => []];

if (!$conn instanceof PDO) {
    $response['message'] = 'Database connection not established.';
    echo json_encode($response);
    exit;
}

if (isset($_GET['jci_number'])) {
    $jci_number = $_GET['jci_number'];

    try {
        // Fetch all payments for the given job card number
        $stmt_payments = $conn->prepare("SELECT p.id as payment_id, p.jc_number, p.supplier_name, p.invoice_number, p.invoice_amount, p.cheque_number, p.ptm_amount, p.pd_acc_number, p.payment_invoice_date,
            pd.payment_category, pd.payment_type, pd.payment_full_partial
            FROM payments p
            LEFT JOIN payment_details pd ON p.id = pd.payment_id
            WHERE p.jc_number = ?");
        $stmt_payments->execute([$jci_number]);
        $payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['payments'] = $payments;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching payments: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Job Card Number (jci_number) not provided.';
}

echo json_encode($response);
?>

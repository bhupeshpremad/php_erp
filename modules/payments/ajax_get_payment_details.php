<?php

include_once '../../config/config.php';

header('Content-Type: application/json');

global $conn;

$response = ['success' => false, 'data' => []];

if (!$conn instanceof PDO) {
    $response['message'] = 'Database connection not established.';
    echo json_encode($response);
    exit;
}

if (isset($_GET['payment_id'])) {
    $payment_id = intval($_GET['payment_id']);

    try {
        // Fetch payment general info
        $stmt_payment = $conn->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt_payment->execute([$payment_id]);
        $payment = $stmt_payment->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            $response['message'] = 'Payment not found.';
            echo json_encode($response);
            exit;
        }

        // Fetch job cards
        $stmt_job_cards = $conn->prepare("SELECT jc_number, jc_amt FROM job_cards WHERE payment_id = ?");
        $stmt_job_cards->execute([$payment_id]);
        $job_cards = $stmt_job_cards->fetchAll(PDO::FETCH_ASSOC);

        // Fetch suppliers and their items
        $stmt_suppliers = $conn->prepare("SELECT id, supplier_name, invoice_number, invoice_amount FROM suppliers WHERE payment_id = ?");
        $stmt_suppliers->execute([$payment_id]);
        $suppliers = $stmt_suppliers->fetchAll(PDO::FETCH_ASSOC);

        foreach ($suppliers as &$supplier) {
            $stmt_items = $conn->prepare("SELECT item_name, item_quantity, item_price, item_amount FROM supplier_items WHERE supplier_id = ?");
            $stmt_items->execute([$supplier['id']]);
            $supplier['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            unset($supplier['id']); // Remove id from output
        }

        // Fetch payment details
        $stmt_payment_details = $conn->prepare("SELECT payment_category, payment_type, cheque_number, pd_acc_number, ptm_amount, payment_invoice_date FROM payment_details WHERE payment_id = ?");
        $stmt_payment_details->execute([$payment_id]);
        $payment_details = $stmt_payment_details->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['data'] = [
            'payment' => $payment,
            'job_cards' => $job_cards,
            'suppliers' => $suppliers,
            'payments' => $payment_details
        ];
    } catch (Exception $e) {
        $response['message'] = 'Error fetching payment details: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Payment ID not provided.';
}

echo json_encode($response);
?>

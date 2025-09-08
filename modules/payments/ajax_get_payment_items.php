<?php

include_once '../../config/config.php';

header('Content-Type: application/json');

global $conn;

$response = ['success' => false, 'items' => []];

if (!$conn instanceof PDO) {
    $response['message'] = 'Database connection not established.';
    echo json_encode($response);
    exit;
}

if (isset($_GET['payment_id'])) {
    $payment_id = intval($_GET['payment_id']);

    try {
        // Get payment details instead of payment_items (table doesn't exist)
        $stmt = $conn->prepare("SELECT payment_category as item_name, ptm_amount as item_amount, payment_type, cheque_number FROM payment_details WHERE payment_id = ?");
        $stmt->execute([$payment_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['items'] = $items;
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Error fetching items: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Payment ID not provided.';
}

echo json_encode($response);
?>
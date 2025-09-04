<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'job_cards' => []];

try {
    // Fetch all job cards with payment status
    $stmt = $conn->prepare("
        SELECT 
            jci.id,
            jci.jci_number,
            jci.jci_type,
            jci.po_id,
            p.po_number,
            p.sell_order_number,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM payments pay WHERE pay.jci_number = jci.jci_number
                ) THEN 'Paid'
                ELSE 'Pending'
            END AS payment_status
        FROM jci_main jci
        LEFT JOIN po_main p ON jci.po_id = p.id
        ORDER BY jci.jci_number ASC
    ");
    $stmt->execute();
    $job_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['job_cards'] = $job_cards;
} catch (Exception $e) {
    $response['message'] = 'Error fetching job cards with payment status: ' . $e->getMessage();
}

echo json_encode($response);
?>

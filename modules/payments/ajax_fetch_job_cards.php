<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'job_cards' => []];

if (isset($_GET['po_id'])) {
    $po_id = intval($_GET['po_id']);
    try {
        // Fetch po_number for given po_id
        $stmt_po = $conn->prepare("SELECT po_number FROM po_main WHERE id = ?");
        $stmt_po->execute([$po_id]);
        $po_number = $stmt_po->fetchColumn();

        error_log("Fetching job cards for po_id: $po_id, po_number: $po_number");

        // Fetch payment ids with the given PO number
        $stmt_payments = $conn->prepare("SELECT id FROM payments WHERE pon_number = ?");
        $stmt_payments->execute([$po_number]);
        $payment_ids = $stmt_payments->fetchAll(PDO::FETCH_COLUMN);

        error_log("Payment IDs found: " . implode(',', $payment_ids));

        if (empty($payment_ids)) {
            error_log("No payment IDs found for po_number: $po_number");
        }

        if (empty($payment_ids)) {
            $response['success'] = true;
            $response['job_cards'] = [];
            echo json_encode($response);
            exit;
        }

        // Fetch job cards linked to these payment ids
        $in_query = implode(',', array_fill(0, count($payment_ids), '?'));
        $stmt_job_cards = $conn->prepare("SELECT jc_number, jc_amt FROM job_cards WHERE payment_id IN ($in_query)");
        $stmt_job_cards->execute($payment_ids);
        $job_cards = $stmt_job_cards->fetchAll(PDO::FETCH_ASSOC);

        if (empty($job_cards)) {
            error_log("No job cards found for payment IDs: " . implode(',', $payment_ids));
            // Fallback: join payments and job_cards to fetch job cards by po_number
            $stmt_join = $conn->prepare("
                SELECT jc.jc_number, jc.jc_amt
                FROM job_cards jc
                JOIN payments p ON jc.payment_id = p.id
                WHERE p.pon_number = ?
            ");
            $stmt_join->execute([$po_number]);
            $job_cards = $stmt_join->fetchAll(PDO::FETCH_ASSOC);
        }

        $response['success'] = true;
        $response['job_cards'] = $job_cards;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching job cards: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'PO number ID not provided.';
}

echo json_encode($response);
?>

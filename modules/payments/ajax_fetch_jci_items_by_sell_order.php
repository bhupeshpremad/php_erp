<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'jci_items' => []];

if (isset($_GET['sell_order_number'])) {
    $sell_order_number = $_GET['sell_order_number'];
    try {
        // Fetch JCI records with the given sell_order_number
        $stmt_jci = $conn->prepare("SELECT id, jci_number, jci_type FROM jci_main WHERE sell_order_number = ?");
        $stmt_jci->execute([$sell_order_number]);
        $jci_records = $stmt_jci->fetchAll(PDO::FETCH_ASSOC);

        $jci_items = [];

        foreach ($jci_records as $jci) {
            // Fetch JCI items for each JCI record
            $stmt_items = $conn->prepare("SELECT contracture_name, labour_cost, quantity, total_amount, delivery_date FROM jci_items WHERE jci_id = ?");
            $stmt_items->execute([$jci['id']]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            if ($items) {
                foreach ($items as $item) {
                    // Add jci_number and jci_type to each item
                    $item['jci_number'] = $jci['jci_number'];
                    $item['jci_type'] = $jci['jci_type'] ?? '';
                    $jci_items[] = $item;
                }
            }
        }

        $response['success'] = true;
        $response['jci_items'] = $jci_items;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching JCI items: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Sell Order Number not provided.';
}

echo json_encode($response);
?>

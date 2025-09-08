<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['jci_number'])) {
        echo json_encode(['error' => 'Missing jci_number']);
        exit;
    }

    $jci_number = $_POST['jci_number'];
    global $conn;

    // Get complete sell order number from sell_order table via po_main
    $sql = "SELECT so.sell_order_number 
            FROM jci_main jci 
            JOIN po_main po ON jci.po_id = po.id 
            JOIN sell_order so ON po.id = so.po_id 
            WHERE jci.jci_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$jci_number]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['sell_order_number'])) {
        echo json_encode(['sell_order_number' => trim($row['sell_order_number'])]);
    } else {
        // Fallback: try to get from po_main directly
        $sql2 = "SELECT po.sell_order_number 
                FROM jci_main jci 
                JOIN po_main po ON jci.po_id = po.id 
                WHERE jci.jci_number = ?";
        
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$jci_number]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($row2) {
            echo json_encode(['sell_order_number' => trim($row2['sell_order_number'])]);
        } else {
            echo json_encode(['error' => 'Sell order number not found']);
        }
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}
?>
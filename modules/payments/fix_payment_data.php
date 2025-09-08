<?php
include_once '../../config/config.php';
global $conn;

// Check if payment ID 4 has any payment_details
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM payment_details WHERE payment_id = 4");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Payment details count for payment ID 4: " . $result['count'] . "<br>";

// If no payment details exist, let's check what should be there
if ($result['count'] == 0) {
    echo "No payment details found. Checking payment record...<br>";
    
    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = 4");
    $stmt->execute();
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        echo "Payment record exists:<br>";
        echo "JCI Number: " . $payment['jci_number'] . "<br>";
        echo "PO Number: " . $payment['po_number'] . "<br>";
        echo "SO Number: " . $payment['sell_order_number'] . "<br>";
        
        // Check if there are any purchase items for this JCI that should have payments
        $stmt = $conn->prepare("
            SELECT pi.* 
            FROM purchase_items pi 
            JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
            WHERE pm.jci_number = ? AND pi.invoice_number IS NOT NULL
        ");
        $stmt->execute([$payment['jci_number']]);
        $purchase_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Purchase items with invoices: " . count($purchase_items) . "<br>";
        
        if (!empty($purchase_items)) {
            echo "Sample purchase item:<br>";
            echo "Supplier: " . $purchase_items[0]['supplier_name'] . "<br>";
            echo "Invoice: " . $purchase_items[0]['invoice_number'] . "<br>";
            echo "Amount: " . $purchase_items[0]['amount'] . "<br>";
        }
    } else {
        echo "Payment record not found!<br>";
    }
}
?>
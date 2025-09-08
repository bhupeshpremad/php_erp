<?php
include_once '../../config/config.php';
global $conn;

$payment_id = 4;

echo "<h3>Debug Payment ID: $payment_id</h3>";

// Check payments table
$stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h4>Payment Record:</h4>";
echo "<pre>" . print_r($payment, true) . "</pre>";

// Check payment_details table
$stmt = $conn->prepare("SELECT * FROM payment_details WHERE payment_id = ?");
$stmt->execute([$payment_id]);
$payment_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h4>Payment Details:</h4>";
echo "<pre>" . print_r($payment_details, true) . "</pre>";

if ($payment) {
    $jci_number = $payment['jci_number'];
    
    // Check purchase_items for this JCI
    $stmt = $conn->prepare("
        SELECT pi.* 
        FROM purchase_items pi 
        JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
        WHERE pm.jci_number = ?
    ");
    $stmt->execute([$jci_number]);
    $purchase_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Purchase Items for JCI $jci_number:</h4>";
    echo "<pre>" . print_r($purchase_items, true) . "</pre>";
}
?>
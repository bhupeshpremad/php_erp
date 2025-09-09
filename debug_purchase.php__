<?php
session_start();
include_once __DIR__ . '/config/config.php';

global $conn;

$purchase_id = 7;

echo "<h2>Debug Purchase ID: $purchase_id</h2>";

// Check purchase_main table
$stmt = $conn->prepare("SELECT * FROM purchase_main WHERE id = ?");
$stmt->execute([$purchase_id]);
$purchase_data = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Purchase Main Data:</h3>";
if ($purchase_data) {
    echo "<pre>" . print_r($purchase_data, true) . "</pre>";
} else {
    echo "<p>No purchase found with ID $purchase_id</p>";
}

// Check purchase_items table
$stmt_items = $conn->prepare("SELECT * FROM purchase_items WHERE purchase_main_id = ?");
$stmt_items->execute([$purchase_id]);
$purchase_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Purchase Items Data:</h3>";
if ($purchase_items) {
    echo "<pre>" . print_r($purchase_items, true) . "</pre>";
} else {
    echo "<p>No purchase items found for purchase ID $purchase_id</p>";
}

// Check if JCI exists
if ($purchase_data && !empty($purchase_data['jci_number'])) {
    $jci_number = $purchase_data['jci_number'];
    echo "<h3>JCI Check for: $jci_number</h3>";
    
    $stmt_jci = $conn->prepare("SELECT * FROM jci_main WHERE jci_number = ?");
    $stmt_jci->execute([$jci_number]);
    $jci_data = $stmt_jci->fetch(PDO::FETCH_ASSOC);
    
    if ($jci_data) {
        echo "<pre>" . print_r($jci_data, true) . "</pre>";
    } else {
        echo "<p>No JCI found with number $jci_number</p>";
    }
}
?>
<?php
// Check saved purchase data
session_start();

// Database connection
$host = 'localhost';
$dbname = 'u404997496_erp';
$username = 'u404997496_erp_u404997496';
$password = 'PUrewood@2025#';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Purchase Main Data for JCI-2025-0004:</h3>";
    $stmt = $conn->prepare("SELECT * FROM purchase_main WHERE jci_number = ?");
    $stmt->execute(['JCI-2025-0004']);
    $purchase_main = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($purchase_main) {
        echo "<pre>";
        print_r($purchase_main);
        echo "</pre>";
        
        $purchase_id = $purchase_main[0]['id'];
        
        echo "<h3>Purchase Items Data:</h3>";
        $stmt = $conn->prepare("SELECT * FROM purchase_items WHERE purchase_main_id = ?");
        $stmt->execute([$purchase_id]);
        $purchase_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<pre>";
        print_r($purchase_items);
        echo "</pre>";
        
        echo "<h3>Wood Items with Dimensions:</h3>";
        $stmt = $conn->prepare("SELECT supplier_name, product_type, product_name, assigned_quantity, price, length_ft, width_ft, thickness_inch FROM purchase_items WHERE purchase_main_id = ? AND product_type = 'Wood'");
        $stmt->execute([$purchase_id]);
        $wood_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<pre>";
        print_r($wood_items);
        echo "</pre>";
        
    } else {
        echo "No purchase data found for JCI-2025-0004";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
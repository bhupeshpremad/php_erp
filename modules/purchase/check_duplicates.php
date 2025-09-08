<?php
require_once __DIR__ . '/../../config/config.php';

// Check duplicates in purchase_items for purchase_main_id = 6
$stmt = $conn->prepare("SELECT id, supplier_name, product_type, product_name, job_card_number FROM purchase_items WHERE purchase_main_id = 6 ORDER BY id");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Purchase Items for ID 6:</h3>";
foreach($items as $item) {
    echo "ID: {$item['id']}, Supplier: {$item['supplier_name']}, Type: {$item['product_type']}, Name: {$item['product_name']}, Job: {$item['job_card_number']}<br>";
}

// Delete exact duplicates keeping the first occurrence
$stmt_delete = $conn->prepare("DELETE p1 FROM purchase_items p1
INNER JOIN purchase_items p2 
WHERE p1.id > p2.id 
AND p1.purchase_main_id = p2.purchase_main_id 
AND p1.supplier_name = p2.supplier_name 
AND p1.product_type = p2.product_type 
AND p1.product_name = p2.product_name 
AND p1.job_card_number = p2.job_card_number
AND p1.purchase_main_id = 6");

$stmt_delete->execute();
echo "<br>Duplicates removed: " . $stmt_delete->rowCount();
?>
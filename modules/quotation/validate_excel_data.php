<?php
function validateExcelData($products) {
    $errors = [];
    $warnings = [];
    
    if (empty($products)) {
        $errors[] = "No product data found";
        return ['errors' => $errors, 'warnings' => $warnings];
    }
    
    foreach ($products as $index => $product) {
        $rowNum = $index + 1;
        
        // Required field validation
        if (empty($product['item_name'])) {
            $errors[] = "Row $rowNum: Item Name is required";
        }
        
        // Numeric field validation
        $numericFields = ['item_w', 'item_d', 'item_h', 'box_w', 'box_d', 'box_h', 'quantity', 'price_usd', 'no_of_packet'];
        foreach ($numericFields as $field) {
            if (!empty($product[$field]) && !is_numeric($product[$field])) {
                $warnings[] = "Row $rowNum: $field should be numeric, got '{$product[$field]}'";
            }
        }
        
        // Business logic validation
        if (!empty($product['quantity']) && floatval($product['quantity']) <= 0) {
            $warnings[] = "Row $rowNum: Quantity should be greater than 0";
        }
        
        if (!empty($product['price_usd']) && floatval($product['price_usd']) < 0) {
            $warnings[] = "Row $rowNum: Price should not be negative";
        }
    }
    
    return ['errors' => $errors, 'warnings' => $warnings];
}

// If called directly via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_data'])) {
    header('Content-Type: application/json');
    
    $products = json_decode($_POST['products'], true);
    $validation = validateExcelData($products);
    
    echo json_encode([
        'success' => empty($validation['errors']),
        'errors' => $validation['errors'],
        'warnings' => $validation['warnings']
    ]);
    exit;
}
?>
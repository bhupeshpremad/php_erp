<?php
session_start();
include '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['supplier_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    $products = [];
    
    // Process rows (skip header row)
    for ($i = 2; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        if (empty($row[3])) continue; // Skip if no item name
        
        $product = [
            'item_name' => $row[3] ?? '',
            'item_code' => $row[4] ?? '',
            'assembly' => $row[5] ?? '',
            'item_w' => floatval($row[6] ?? 0),
            'item_d' => floatval($row[7] ?? 0),
            'item_h' => floatval($row[8] ?? 0),
            'box_w' => floatval($row[9] ?? 0),
            'box_d' => floatval($row[10] ?? 0),
            'box_h' => floatval($row[11] ?? 0),
            'cbm' => floatval($row[12] ?? 0),
            'wood_type' => $row[13] ?? '',
            'packets' => intval($row[14] ?? 1),
            'quantity' => intval($row[15] ?? 1),
            'price_usd' => floatval($row[16] ?? 0),
            'total_usd' => floatval($row[17] ?? 0),
            'comments' => $row[18] ?? ''
        ];
        
        // Auto calculate CBM and total
        if ($product['cbm'] == 0) {
            $product['cbm'] = ($product['box_w'] * $product['box_d'] * $product['box_h']) / 1000000;
        }
        if ($product['total_usd'] == 0) {
            $product['total_usd'] = $product['quantity'] * $product['price_usd'];
        }
        
        $products[] = $product;
    }
    
    echo json_encode(['success' => true, 'data' => $products]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
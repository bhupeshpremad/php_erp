<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$jci_number = $_GET['jci'] ?? 'JCI-2025-001';

global $conn;

try {
    // Get purchase main data
    $stmt_main = $conn->prepare("SELECT * FROM purchase_main WHERE jci_number = ?");
    $stmt_main->execute([$jci_number]);
    $purchase_main = $stmt_main->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase_main) {
        echo json_encode(['error' => 'No purchase found for JCI: ' . $jci_number]);
        exit;
    }
    
    // Get purchase items with all details
    $stmt_items = $conn->prepare("
        SELECT *, 
        TRIM(supplier_name) as supplier_name_clean, 
        TRIM(product_type) as product_type_clean, 
        TRIM(product_name) as product_name_clean, 
        TRIM(job_card_number) as job_card_number_clean,
        COALESCE(length_ft, 0) as length_ft,
        COALESCE(width_ft, 0) as width_ft,
        COALESCE(thickness_inch, 0) as thickness_inch
        FROM purchase_items 
        WHERE purchase_main_id = ?
        ORDER BY id
    ");
    $stmt_items->execute([$purchase_main['id']]);
    $purchase_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    
    // Get BOM data for comparison
    $stmt_jci = $conn->prepare("SELECT bom_id FROM jci_main WHERE jci_number = ?");
    $stmt_jci->execute([$jci_number]);
    $jci_row = $stmt_jci->fetch(PDO::FETCH_ASSOC);
    
    $bom_items = [];
    if ($jci_row && $jci_row['bom_id']) {
        $bom_id = $jci_row['bom_id'];
        
        // Get Wood items
        $stmt_wood = $conn->prepare("SELECT '' as supplier_name, 'Wood' as product_type, woodtype as product_name, length_ft, width_ft, thickness_inch, quantity, price FROM bom_wood WHERE bom_main_id = ?");
        $stmt_wood->execute([$bom_id]);
        $wood_items = $stmt_wood->fetchAll(PDO::FETCH_ASSOC);
        
        // Get other items
        $stmt_glow = $conn->prepare("SELECT '' as supplier_name, 'Glow Type' as product_type, glowtype as product_name, quantity, price FROM bom_glow WHERE bom_main_id = ?");
        $stmt_glow->execute([$bom_id]);
        $glow_items = $stmt_glow->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt_hardware = $conn->prepare("SELECT '' as supplier_name, 'Item Name' as product_type, itemname as product_name, quantity, price FROM bom_hardware WHERE bom_main_id = ?");
        $stmt_hardware->execute([$bom_id]);
        $hardware_items = $stmt_hardware->fetchAll(PDO::FETCH_ASSOC);
        
        $bom_items = array_merge($wood_items, $glow_items, $hardware_items);
    }
    
    echo json_encode([
        'success' => true,
        'jci_number' => $jci_number,
        'purchase_main' => $purchase_main,
        'purchase_items_count' => count($purchase_items),
        'purchase_items' => $purchase_items,
        'bom_items_count' => count($bom_items),
        'bom_items' => $bom_items,
        'matching_analysis' => analyzeMatching($purchase_items, $bom_items)
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function analyzeMatching($purchase_items, $bom_items) {
    $analysis = [];
    
    foreach ($bom_items as $bom_item) {
        $matches = [];
        foreach ($purchase_items as $purchase_item) {
            $match_score = 0;
            $match_details = [];
            
            // Type match
            if ($purchase_item['product_type_clean'] === $bom_item['product_type']) {
                $match_score += 20;
                $match_details[] = 'type_match';
            }
            
            // Name match
            if ($purchase_item['product_name_clean'] === $bom_item['product_name']) {
                $match_score += 20;
                $match_details[] = 'name_match';
            }
            
            // For Wood items, check dimensions
            if ($bom_item['product_type'] === 'Wood') {
                if (isset($bom_item['length_ft']) && isset($purchase_item['length_ft'])) {
                    if (abs(floatval($purchase_item['length_ft']) - floatval($bom_item['length_ft'])) < 0.01) {
                        $match_score += 15;
                        $match_details[] = 'length_match';
                    }
                }
                if (isset($bom_item['width_ft']) && isset($purchase_item['width_ft'])) {
                    if (abs(floatval($purchase_item['width_ft']) - floatval($bom_item['width_ft'])) < 0.01) {
                        $match_score += 15;
                        $match_details[] = 'width_match';
                    }
                }
                if (isset($bom_item['thickness_inch']) && isset($purchase_item['thickness_inch'])) {
                    if (abs(floatval($purchase_item['thickness_inch']) - floatval($bom_item['thickness_inch'])) < 0.01) {
                        $match_score += 15;
                        $match_details[] = 'thickness_match';
                    }
                }
            }
            
            // Quantity match
            if (abs(floatval($purchase_item['assigned_quantity']) - floatval($bom_item['quantity'])) < 0.001) {
                $match_score += 10;
                $match_details[] = 'quantity_match';
            }
            
            // Price match
            if (abs(floatval($purchase_item['price']) - floatval($bom_item['price'])) < 0.01) {
                $match_score += 5;
                $match_details[] = 'price_match';
            }
            
            if ($match_score > 30) { // Threshold for considering it a match
                $matches[] = [
                    'purchase_id' => $purchase_item['id'],
                    'match_score' => $match_score,
                    'match_details' => $match_details,
                    'supplier_name' => $purchase_item['supplier_name_clean'],
                    'invoice_number' => $purchase_item['invoice_number'],
                    'invoice_image' => $purchase_item['invoice_image']
                ];
            }
        }
        
        $analysis[] = [
            'bom_item' => $bom_item,
            'matches_found' => count($matches),
            'matches' => $matches
        ];
    }
    
    return $analysis;
}
?>
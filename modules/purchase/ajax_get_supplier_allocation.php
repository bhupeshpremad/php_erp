<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$jci_number = filter_var($_POST['jci_number'] ?? '', FILTER_SANITIZE_STRING);

if (empty($jci_number)) {
    echo json_encode(['success' => false, 'error' => 'JCI number is required']);
    exit;
}

global $conn;

try {
    // Get BOM data for the JCI
    $stmt_bom = $conn->prepare("
        SELECT 
            'Wood' as product_type,
            bw.woodtype as product_name,
            bw.quantity as bom_quantity,
            bw.price as bom_price
        FROM bom_wood bw 
        JOIN bom_main bm ON bw.bom_main_id = bm.id 
        JOIN jci_main jm ON bm.id = jm.bom_id 
        WHERE jm.jci_number = ?
        
        UNION ALL
        
        SELECT 
            'Hardware' as product_type,
            bh.itemname as product_name,
            bh.quantity as bom_quantity,
            bh.price as bom_price
        FROM bom_hardware bh 
        JOIN bom_main bm ON bh.bom_main_id = bm.id 
        JOIN jci_main jm ON bm.id = jm.bom_id 
        WHERE jm.jci_number = ?
        
        UNION ALL
        
        SELECT 
            'Glow' as product_type,
            bg.glowtype as product_name,
            bg.quantity as bom_quantity,
            bg.price as bom_price
        FROM bom_glow bg 
        JOIN bom_main bm ON bg.bom_main_id = bm.id 
        JOIN jci_main jm ON bm.id = jm.bom_id 
        WHERE jm.jci_number = ?
        
        UNION ALL
        
        SELECT 
            'Plynydf' as product_type,
            CONCAT('Ply ', bp.width, 'x', bp.length) as product_name,
            bp.quantity as bom_quantity,
            bp.price as bom_price
        FROM bom_plynydf bp 
        JOIN bom_main bm ON bp.bom_main_id = bm.id 
        JOIN jci_main jm ON bm.id = jm.bom_id 
        WHERE jm.jci_number = ?
    ");
    
    $stmt_bom->execute([$jci_number, $jci_number, $jci_number, $jci_number]);
    $bom_items = $stmt_bom->fetchAll(PDO::FETCH_ASSOC);

    // Get existing purchase allocations
    $stmt_purchase = $conn->prepare("
        SELECT 
            pi.product_type,
            pi.product_name,
            pi.job_card_number,
            pi.supplier_name,
            pi.assigned_quantity,
            pi.price,
            pi.total,
            pi.supplier_sequence,
            pi.invoice_number,
            pi.builty_number,
            pi.item_approval_status
        FROM purchase_items pi
        JOIN purchase_main pm ON pi.purchase_main_id = pm.id
        WHERE pm.jci_number = ?
        ORDER BY pi.product_name, pi.supplier_sequence
    ");
    
    $stmt_purchase->execute([$jci_number]);
    $purchase_items = $stmt_purchase->fetchAll(PDO::FETCH_ASSOC);

    // Build allocation summary
    $allocation_summary = [];
    
    foreach ($bom_items as $bom_item) {
        $key = $bom_item['product_type'] . '_' . $bom_item['product_name'];
        
        if (!isset($allocation_summary[$key])) {
            $allocation_summary[$key] = [
                'product_type' => $bom_item['product_type'],
                'product_name' => $bom_item['product_name'],
                'bom_quantity' => floatval($bom_item['bom_quantity']),
                'bom_price' => floatval($bom_item['bom_price']),
                'total_assigned' => 0,
                'remaining_quantity' => floatval($bom_item['bom_quantity']),
                'suppliers' => [],
                'allocation_status' => 'pending'
            ];
        }
    }

    // Add purchase allocations
    foreach ($purchase_items as $purchase_item) {
        $key = $purchase_item['product_type'] . '_' . $purchase_item['product_name'];
        
        if (isset($allocation_summary[$key])) {
            $assigned_qty = floatval($purchase_item['assigned_quantity']);
            $allocation_summary[$key]['total_assigned'] += $assigned_qty;
            $allocation_summary[$key]['remaining_quantity'] -= $assigned_qty;
            
            $allocation_summary[$key]['suppliers'][] = [
                'name' => $purchase_item['supplier_name'],
                'sequence' => intval($purchase_item['supplier_sequence']),
                'quantity' => $assigned_qty,
                'price' => floatval($purchase_item['price']),
                'total' => floatval($purchase_item['total']),
                'job_card' => $purchase_item['job_card_number'],
                'invoice_number' => $purchase_item['invoice_number'],
                'builty_number' => $purchase_item['builty_number'],
                'approval_status' => $purchase_item['item_approval_status']
            ];
        }
    }

    // Determine allocation status for each product
    foreach ($allocation_summary as $key => &$item) {
        $remaining = $item['remaining_quantity'];
        
        if ($remaining < -0.001) {
            $item['allocation_status'] = 'over_allocated';
        } elseif (abs($remaining) <= 0.001) {
            $item['allocation_status'] = 'fully_allocated';
        } elseif ($item['total_assigned'] > 0) {
            $item['allocation_status'] = 'partially_allocated';
        } else {
            $item['allocation_status'] = 'not_allocated';
        }
        
        // Sort suppliers by sequence
        usort($item['suppliers'], function($a, $b) {
            return $a['sequence'] - $b['sequence'];
        });
    }

    echo json_encode([
        'success' => true,
        'jci_number' => $jci_number,
        'allocation_summary' => array_values($allocation_summary),
        'summary_stats' => [
            'total_products' => count($allocation_summary),
            // Optimized single-pass counting
            ...array_reduce($allocation_summary, function($stats, $item) {
                $stats[$item['allocation_status']]++;
                return $stats;
            }, [
                'fully_allocated' => 0,
                'partially_allocated' => 0, 
                'not_allocated' => 0,
                'over_allocated' => 0
            ])
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Application error: ' . $e->getMessage()]);
}
?>
<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$jci_number = $_POST['jci_number'] ?? null;
$po_id = $_POST['po_id'] ?? null;

if (!$jci_number || !$po_id) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

global $conn;

try {
    $response = ['success' => true];
    
    // Fetch PO number
    $stmt_po = $conn->prepare("SELECT po_number FROM po_main WHERE id = ?");
    $stmt_po->execute([$po_id]);
    $po_data = $stmt_po->fetch(PDO::FETCH_ASSOC);
    $response['po_data'] = $po_data;
    
    // Fetch BOM number
    $stmt_jci = $conn->prepare("SELECT bom_id FROM jci_main WHERE jci_number = ?");
    $stmt_jci->execute([$jci_number]);
    $jci_data = $stmt_jci->fetch(PDO::FETCH_ASSOC);
    
    if ($jci_data && $jci_data['bom_id']) {
        $stmt_bom = $conn->prepare("SELECT bom_number FROM bom_main WHERE id = ?");
        $stmt_bom->execute([$jci_data['bom_id']]);
        $bom_data = $stmt_bom->fetch(PDO::FETCH_ASSOC);
        $response['bom_data'] = $bom_data;
    }
    
    // Fetch job cards using existing logic
    $stmt_jci_id = $conn->prepare("SELECT id FROM jci_main WHERE jci_number = ?");
    $stmt_jci_id->execute([$jci_number]);
    $jci_result = $stmt_jci_id->fetch(PDO::FETCH_ASSOC);
    
    if ($jci_result) {
        $stmt_job_cards = $conn->prepare("SELECT job_card_number FROM jci_items WHERE jci_id = ?");
        $stmt_job_cards->execute([$jci_result['id']]);
        $job_cards = $stmt_job_cards->fetchAll(PDO::FETCH_COLUMN);
        $response['job_cards'] = $job_cards;
    } else {
        $response['job_cards'] = [];
    }
    
    // Fetch BOM items
    if ($jci_data && $jci_data['bom_id']) {
        $stmt_bom_items = $conn->prepare("
            SELECT 'Glow' as product_type, glowtype as product_name, quantity, price 
            FROM bom_glow WHERE bom_main_id = ?
            UNION ALL
            SELECT 'Hardware' as product_type, itemname as product_name, quantity, price 
            FROM bom_hardware WHERE bom_main_id = ?
            UNION ALL
            SELECT 'Plynydf' as product_type, 'Plynydf' as product_name, quantity, price 
            FROM bom_plynydf WHERE bom_main_id = ?
            UNION ALL
            SELECT 'Wood' as product_type, woodtype as product_name, quantity, price 
            FROM bom_wood WHERE bom_main_id = ?
        ");
        $stmt_bom_items->execute([$jci_data['bom_id'], $jci_data['bom_id'], $jci_data['bom_id'], $jci_data['bom_id']]);
        $bom_items = $stmt_bom_items->fetchAll(PDO::FETCH_ASSOC);
        $response['bom_items'] = $bom_items;
    } else {
        $response['bom_items'] = [];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
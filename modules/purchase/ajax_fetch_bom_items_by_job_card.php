<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_POST['jci_number'])) {
    echo json_encode(['error' => 'Missing jci_number']);
    exit;
}

$jci_number = $_POST['jci_number'];

global $conn;

try {
    // Get bom_id from jci_main
    $stmt_jci = $conn->prepare("SELECT bom_id FROM jci_main WHERE jci_number = ?");
    $stmt_jci->execute([$jci_number]);
    $jci_row = $stmt_jci->fetch(PDO::FETCH_ASSOC);

    if (!$jci_row || !$jci_row['bom_id']) {
        echo json_encode(['error' => 'BOM not found for this JCI']);
        exit;
    }

    $bom_id = $jci_row['bom_id'];

    // Fetch BOM items from all BOM tables by bom_main_id
    $bom_glow_data = [];
    $stmt_glow = $conn->prepare("SELECT '' as supplier_name, 'Glow' as product_type, glowtype as product_name, quantity, price FROM bom_glow WHERE bom_main_id = ?");
    $stmt_glow->execute([$bom_id]);
    $bom_glow_data = $stmt_glow->fetchAll(PDO::FETCH_ASSOC);

    $bom_hardware_data = [];
    $stmt_hardware = $conn->prepare("SELECT '' as supplier_name, 'Hardware' as product_type, itemname as product_name, quantity, price FROM bom_hardware WHERE bom_main_id = ?");
    $stmt_hardware->execute([$bom_id]);
    $bom_hardware_data = $stmt_hardware->fetchAll(PDO::FETCH_ASSOC);

    $bom_plynydf_data = [];
    $stmt_plynydf = $conn->prepare("SELECT '' as supplier_name, 'Plynydf' as product_type, 'Plynydf' as product_name, quantity, width, length, price, total FROM bom_plynydf WHERE bom_main_id = ?");
    $stmt_plynydf->execute([$bom_id]);
    $bom_plynydf_data = $stmt_plynydf->fetchAll(PDO::FETCH_ASSOC);

    $bom_wood_data = [];
    $stmt_wood = $conn->prepare("SELECT '' as supplier_name, 'Wood' as product_type, woodtype as product_name, length_ft, width_ft, thickness_inch, quantity, price, cft, total FROM bom_wood WHERE bom_main_id = ?");
    $stmt_wood->execute([$bom_id]);
    $bom_wood_data = $stmt_wood->fetchAll(PDO::FETCH_ASSOC);

    // Combine all BOM items
    $all_bom_items = array_merge($bom_glow_data, $bom_hardware_data, $bom_plynydf_data, $bom_wood_data);

    echo json_encode($all_bom_items);
    exit;

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>

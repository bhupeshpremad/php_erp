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
    $stmt_jci = $conn->prepare("SELECT id, po_id, bom_id FROM jci_main WHERE jci_number = ?");
    $stmt_jci->execute([$jci_number]);
    $jci_row = $stmt_jci->fetch(PDO::FETCH_ASSOC);

    if (!$jci_row) {
        echo json_encode(['error' => 'JCI Number not found']);
        exit;
    }

    $po_id = $jci_row['po_id'];
    $bom_id = $jci_row['bom_id'];

    $po_number = '';
    if ($po_id) {
        $stmt_po = $conn->prepare("SELECT po_number FROM po_main WHERE id = ?");
        $stmt_po->execute([$po_id]);
        $po_row = $stmt_po->fetch(PDO::FETCH_ASSOC);
        if ($po_row) {
            $po_number = $po_row['po_number'];
        }
    }

    $bom_number = '';
    if ($bom_id) {
        $stmt_bom = $conn->prepare("SELECT bom_number FROM bom_main WHERE id = ?");
        $stmt_bom->execute([$bom_id]);
        $bom_row = $stmt_bom->fetch(PDO::FETCH_ASSOC);
        if ($bom_row) {
            $bom_number = $bom_row['bom_number'];
        }
    }

    $bom_glow_data = [];
    if ($bom_id) {
        $stmt_glow = $conn->prepare("SELECT * FROM bom_glow WHERE bom_main_id = ?");
        $stmt_glow->execute([$bom_id]);
        $bom_glow_data = $stmt_glow->fetchAll(PDO::FETCH_ASSOC);
    }

    $bom_hardware_data = [];
    if ($bom_id) {
        $stmt_hardware = $conn->prepare("SELECT * FROM bom_hardware WHERE bom_main_id = ?");
        $stmt_hardware->execute([$bom_id]);
        $bom_hardware_data = $stmt_hardware->fetchAll(PDO::FETCH_ASSOC);
    }

    $bom_plynydf_data = [];
    if ($bom_id) {
        $stmt_plynydf = $conn->prepare("SELECT * FROM bom_plynydf WHERE bom_main_id = ?");
        $stmt_plynydf->execute([$bom_id]);
        $bom_plynydf_data = $stmt_plynydf->fetchAll(PDO::FETCH_ASSOC);
    }

    $bom_wood_data = [];
    if ($bom_id) {
        $stmt_wood = $conn->prepare("SELECT * FROM bom_wood WHERE bom_main_id = ?");
        $stmt_wood->execute([$bom_id]);
        $bom_wood_data = $stmt_wood->fetchAll(PDO::FETCH_ASSOC);
    }

    $job_card_count = 0;
    if ($jci_row && isset($jci_row['id'])) {
        $stmt_job_card = $conn->prepare("SELECT COUNT(*) as job_card_count FROM jci_items WHERE jci_id = ?");
        $stmt_job_card->execute([$jci_row['id']]);
        $job_card_count_row = $stmt_job_card->fetch(PDO::FETCH_ASSOC);
        if ($job_card_count_row) {
            $job_card_count = intval($job_card_count_row['job_card_count']);
        }
    }

    $response = [
        'po_number' => $po_number,
        'bom_number' => $bom_number,
        'bom_glow' => $bom_glow_data,
        'bom_hardware' => $bom_hardware_data,
        'bom_plynydf' => $bom_plynydf_data,
        'bom_wood' => $bom_wood_data,
        'job_card_count' => $job_card_count,
    ];

    echo json_encode($response);
    exit;
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
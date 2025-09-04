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
    $stmt = $conn->prepare("SELECT bom_id FROM jci_main WHERE jci_number = ?");
    $stmt->execute([$jci_number]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !$row['bom_id']) {
        echo json_encode(['error' => 'BOM not found for this JCI']);
        exit;
    }

    $bom_id = $row['bom_id'];

    $stmt_bom = $conn->prepare("SELECT bom_number FROM bom_main WHERE id = ?");
    $stmt_bom->execute([$bom_id]);
    $bom_row = $stmt_bom->fetch(PDO::FETCH_ASSOC);

    if (!$bom_row || !$bom_row['bom_number']) {
        echo json_encode(['error' => 'BOM number not found']);
        exit;
    }

    echo json_encode(['bom_number' => $bom_row['bom_number']]);
    exit;

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>

<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_POST['jci_number'])) {
    echo json_encode(['error' => 'Missing jci_number']);
    exit;
}

$jci_number = $_POST['jci_number'];

global $conn;

// Get jci_id from jci_number
$sql = "SELECT id FROM jci_main WHERE jci_number = :jci_number";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':jci_number', $jci_number, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) === 0) {
    echo json_encode(['error' => 'JCI number not found']);
    exit;
}

$jci_id = $result[0]['id'];

$sql = "SELECT job_card_number FROM jci_items WHERE jci_id = :jci_id";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':jci_id', $jci_id, PDO::PARAM_INT);
$stmt->execute();
$job_cards = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['job_cards' => $job_cards]);
exit;
?>

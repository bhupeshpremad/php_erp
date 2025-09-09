<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Debug individual save issue
$po_number = $_POST['po_number'] ?? null;
$jci_number = $_POST['jci_number'] ?? null;
$items_json = $_POST['items_json'] ?? '[]';
$items = json_decode($items_json, true);

echo json_encode([
    'debug' => true,
    'received_data' => [
        'po_number' => $po_number,
        'jci_number' => $jci_number,
        'items_count' => count($items),
        'items' => $items
    ],
    'post_data' => $_POST,
    'files_data' => array_keys($_FILES)
]);
?>
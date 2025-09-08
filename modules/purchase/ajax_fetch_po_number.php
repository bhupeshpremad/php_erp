<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['po_id'])) {
        echo json_encode(['error' => 'Missing po_id']);
        exit;
    }

    $po_id = intval($_POST['po_id']);

    global $conn;

    // Using PDO prepare and execute since config.php uses PDO
    $sql = "SELECT po_number FROM po_main WHERE id = :po_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':po_id', $po_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['error' => 'PO ID not found']);
        exit;
    }

    echo json_encode(['po_number' => $row['po_number']]);
    exit;
} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    exit;
}
?>

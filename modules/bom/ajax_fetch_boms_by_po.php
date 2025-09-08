<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$po_id = (int)($_POST['po_id'] ?? 0);

if ($po_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid PO ID provided.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, bom_number FROM bom_main WHERE po_id = ?");
    $stmt->execute([$po_id]);
    $boms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'boms' => $boms]);
    exit;
} catch (Exception $e) {
    error_log("Error fetching BOMs by PO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred while fetching BOMs. Please try again later.']);
    exit;
}
?>

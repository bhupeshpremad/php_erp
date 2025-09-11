<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_POST['jci_number'])) {
    echo json_encode(['success' => false, 'message' => 'Missing jci_number']);
    exit;
}

$jci_number = $_POST['jci_number'];

global $conn;

try {
    // Check if any purchase items exist for this JCI with invoice uploaded
    $stmt = $conn->prepare("
        SELECT COUNT(*) as invoice_count
        FROM purchase_items pi
        JOIN purchase_main pm ON pi.purchase_main_id = pm.id
        WHERE pm.jci_number = ? AND pi.invoice_image IS NOT NULL AND pi.invoice_image != ''
    ");
    $stmt->execute([$jci_number]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $status = ($result['invoice_count'] > 0) ? 'Uploaded' : 'Not Uploaded';

    echo json_encode(['success' => true, 'status' => $status]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

<?php

include_once __DIR__ . '/../../config/config.php';
require_once ROOT_DIR_PATH . 'core/NotificationSystem.php';

header('Content-Type: application/json');

if (!isset($conn) || !$conn instanceof PDO) {
    echo json_encode(['success' => false, 'message' => 'Database connection not initialized.']);
    exit;
}

NotificationSystem::init($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$po_id = (int)($_POST['po_id'] ?? 0);

if ($po_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Purchase Order ID.']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt_check = $conn->prepare("SELECT status, is_locked, po_number FROM po_main WHERE id = :po_id"); // Fetch po_number
    $stmt_check->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $po_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$po_data) {
        throw new Exception("Purchase Order not found.");
    }
    if ($po_data['is_locked'] == 1) {
        throw new Exception("Purchase Order is already locked.");
    }
    if ($po_data['status'] != 'Approved') {
        throw new Exception("Only approved Purchase Orders can be locked.");
    }

    $stmt_update = $conn->prepare("UPDATE po_main SET status = 'Locked', is_locked = 1, updated_at = NOW() WHERE id = :po_id");
    $stmt_update->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_update->execute();

    $conn->commit();

    // Send notification to superadmin
    NotificationSystem::autoNotify('po', 'locked', [
        'id' => $po_id,
        'po_number' => $po_data['po_number']
    ]);

    echo json_encode(['success' => true, 'message' => 'Purchase Order locked successfully. It cannot be edited now.']);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error locking PO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
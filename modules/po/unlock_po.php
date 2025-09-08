<?php

include_once __DIR__ . '/../../config/config.php';
require_once ROOT_DIR_PATH . 'core/NotificationSystem.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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
    $stmt_check = $conn->prepare("SELECT is_locked, po_number FROM po_main WHERE id = :po_id"); // Fetch po_number
    $stmt_check->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $po = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$po) {
        throw new Exception('Purchase Order not found.');
    }

    if ((int)$po['is_locked'] === 0) {
        echo json_encode(['success' => true, 'message' => 'PO is already unlocked.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE po_main SET is_locked = 0, status = 'Approved', updated_at = NOW() WHERE id = :po_id");
    $stmt->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt->execute();

    // Send notification to superadmin
    NotificationSystem::autoNotify('po', 'unlocked', [
        'id' => $po_id,
        'po_number' => $po['po_number']
    ]);

    echo json_encode(['success' => true, 'message' => 'Purchase Order unlocked successfully.']);
} catch (Exception $e) {
    error_log('Unlock PO error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;



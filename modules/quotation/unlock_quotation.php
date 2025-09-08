<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
require_once ROOT_DIR_PATH . 'core/LockSystem.php';
require_once ROOT_DIR_PATH . 'core/NotificationSystem.php'; // Include NotificationSystem

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if (!isset($conn) || !$conn instanceof PDO) {
    echo json_encode(['success' => false, 'message' => 'Database connection not initialized.']);
    exit;
}

NotificationSystem::init($conn); // Initialize NotificationSystem

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$quotation_id = $_POST['quotation_id'] ?? null;

if (empty($quotation_id)) {
    echo json_encode(['success' => false, 'message' => 'Quotation ID is required.']);
    exit;
}

$user_id = $_SESSION['user_id']; // This is actually not used in unlock, but good to have context

try {
    $lockSystem = new LockSystem($conn);
    
    // Check current status before unlocking
    $lockInfo = $lockSystem->getLockInfo('quotations', $quotation_id);
    
    if ($lockInfo && !$lockInfo['is_locked']) {
        echo json_encode(['success' => false, 'message' => 'Quotation is already unlocked.']);
        exit;
    }

    if ($lockSystem->unlock('quotations', $quotation_id, $user_id)) {
        // Fetch quotation number for notification
        $stmt = $conn->prepare("SELECT quotation_number FROM quotations WHERE id = ?");
        $stmt->execute([$quotation_id]);
        $quotation_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $quotation_number = $quotation_data['quotation_number'] ?? 'N/A';

        NotificationSystem::autoNotify('quotation', 'unlocked', [
            'id' => $quotation_id,
            'quotation_number' => $quotation_number
        ]);

        echo json_encode(['success' => true, 'message' => 'Quotation unlocked successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unlock quotation.']);
    }

} catch (Exception $e) {
    error_log('Unlock Quotation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

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

$user_id = $_SESSION['user_id'];

try {
    $lockSystem = new LockSystem($conn);
    
    // Check current status before locking
    $lockInfo = $lockSystem->getLockInfo('quotations', $quotation_id);
    
    if ($lockInfo && $lockInfo['is_locked']) {
        echo json_encode(['success' => false, 'message' => 'Quotation is already locked.']);
        exit;
    }

    if ($lockSystem->lock('quotations', $quotation_id, $user_id)) {
        // Fetch quotation number for notification
        $stmt = $conn->prepare("SELECT quotation_number FROM quotations WHERE id = ?");
        $stmt->execute([$quotation_id]);
        $quotation_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $quotation_number = $quotation_data['quotation_number'] ?? 'N/A';

        NotificationSystem::autoNotify('quotation', 'locked', [
            'id' => $quotation_id,
            'quotation_number' => $quotation_number
        ]);

        // Ensure a PI is created for approved + locked quotations
        // Check approval status
        $stmtChk = $conn->prepare("SELECT approve FROM quotations WHERE id = ?");
        $stmtChk->execute([$quotation_id]);
        $approveRow = $stmtChk->fetch(PDO::FETCH_ASSOC);
        $isApproved = (int)($approveRow['approve'] ?? 0) === 1;
        if ($isApproved) {
            // Check if PI already exists
            $stmtPi = $conn->prepare("SELECT pi_id FROM pi WHERE quotation_id = ?");
            $stmtPi->execute([$quotation_id]);
            $piExists = $stmtPi->fetchColumn();
            if (!$piExists) {
                // Generate next PI number by year sequence: PI-YYYY-0001
                $year = date('Y');
                $stmtMax = $conn->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(pi_number, '-', -1) AS UNSIGNED)) AS max_seq
                                            FROM pi WHERE pi_number LIKE ?");
                $like = 'PI-' . $year . '-%';
                $stmtMax->execute([$like]);
                $maxSeq = (int)($stmtMax->fetchColumn() ?: 0);
                $nextSeq = $maxSeq + 1;
                $piNumber = 'PI-' . $year . '-' . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
                $stmtIns = $conn->prepare("INSERT INTO pi (quotation_id, quotation_number, pi_number, status, date_of_pi_raised) VALUES (?, ?, ?, 'Active', ?)");
                $stmtIns->execute([$quotation_id, $quotation_number, $piNumber, date('Y-m-d')]);

                // Optional notification
                NotificationSystem::autoNotify('pi', 'created', [
                    'id' => $conn->lastInsertId(),
                    'pi_number' => $piNumber
                ]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Quotation locked successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to lock quotation.']);
    }

} catch (Exception $e) {
    error_log('Lock Quotation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

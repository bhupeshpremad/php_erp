<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$purchase_id = $_POST['purchase_id'] ?? null;
$action = $_POST['action'] ?? null; // 'send_for_approval' or 'approve'

if (!$purchase_id || !$action) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters.']);
    exit;
}

global $conn;

$new_status = '';
if ($action === 'send_for_approval') {
    $new_status = 'sent_for_approval';
} elseif ($action === 'approve') {
    $new_status = 'approved';
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action specified.']);
    exit;
}

try {
    // Add approval_status column if it doesn't exist
    $conn->exec("ALTER TABLE purchase_main ADD COLUMN IF NOT EXISTS approval_status VARCHAR(50) DEFAULT 'pending'");
    
    $stmt = $conn->prepare("UPDATE purchase_main SET approval_status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_status, $purchase_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Purchase status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Purchase not found or status already updated.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Application error: ' . $e->getMessage()]);
}



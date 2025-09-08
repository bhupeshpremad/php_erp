<?php
include_once __DIR__ . '/../../config/config.php';
global $conn;

header('Content-Type: application/json');

// Support both purchase-level uploads and per-item uploads
$purchase_id = isset($_POST['purchase_id']) ? intval($_POST['purchase_id']) : 0;
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$response = ['success' => false, 'error' => '', 'message' => ''];

try {
    // Per-item upload mode
    if ($item_id > 0) {
        // Ensure item exists and belongs to provided purchase if purchase_id given
        if ($purchase_id > 0) {
            $stmtChk = $conn->prepare("SELECT id FROM purchase_items WHERE id = ? AND purchase_main_id = ?");
            $stmtChk->execute([$item_id, $purchase_id]);
        } else {
            $stmtChk = $conn->prepare("SELECT id FROM purchase_items WHERE id = ?");
            $stmtChk->execute([$item_id]);
        }
        if (!$stmtChk->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Purchase item not found']);
            exit;
        }

        // Directories as used by other modules (payments)
        $invoice_dir = __DIR__ . '/uploads/invoice/';
        $builty_dir  = __DIR__ . '/uploads/Builty/';
        if (!is_dir($invoice_dir)) { mkdir($invoice_dir, 0755, true); }
        if (!is_dir($builty_dir))  { mkdir($builty_dir, 0755, true); }

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB
        $sanitize = function($name) { $name = basename($name); return preg_replace('/[^A-Za-z0-9._-]/', '_', $name); };

        $updates = [];
        $params = [];

        if (isset($_FILES['invoice_image']) && $_FILES['invoice_image']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['invoice_image'];
            if (!in_array($f['type'], $allowed_types) || $f['size'] > $max_file_size) {
                echo json_encode(['success' => false, 'error' => 'Invalid or too large invoice image']);
                exit;
            }
            $name = uniqid('invoice_') . '_' . $sanitize($f['name']);
            if (!move_uploaded_file($f['tmp_name'], $invoice_dir . $name)) {
                echo json_encode(['success' => false, 'error' => 'Failed to save invoice image']);
                exit;
            }
            $updates[] = 'invoice_image = ?';
            $params[] = $name;
        }

        if (isset($_FILES['builty_image']) && $_FILES['builty_image']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['builty_image'];
            if (!in_array($f['type'], $allowed_types) || $f['size'] > $max_file_size) {
                echo json_encode(['success' => false, 'error' => 'Invalid or too large builty image']);
                exit;
            }
            $name = uniqid('builty_') . '_' . $sanitize($f['name']);
            if (!move_uploaded_file($f['tmp_name'], $builty_dir . $name)) {
                echo json_encode(['success' => false, 'error' => 'Failed to save builty image']);
                exit;
            }
            $updates[] = 'builty_image = ?';
            $params[] = $name;
        }

        if (empty($updates)) {
            echo json_encode(['success' => false, 'error' => 'No files were uploaded']);
            exit;
        }

        $sql = 'UPDATE purchase_items SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = ?';
        $params[] = $item_id;
        $stmtU = $conn->prepare($sql);
        $stmtU->execute($params);

        echo json_encode(['success' => true, 'message' => 'Item documents uploaded successfully']);
        exit;
    }

    // Purchase-level upload mode
    if ($purchase_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Purchase ID is required']);
        exit;
    }

    // Check if purchase exists
    $stmt = $conn->prepare("SELECT id FROM purchase_main WHERE id = ?");
    $stmt->execute([$purchase_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Purchase record not found']);
        exit;
    }

    $upload_dir = __DIR__ . '/../../uploads/purchase/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $updates = [];
    $params = [];

    // Handle invoice file upload
    if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['invoice_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'invoice_' . $purchase_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $updates[] = "invoice_file = ?";
            $params[] = 'uploads/purchase/' . $filename;
            $updates[] = "invoice_number = ?";
            $params[] = 'INV-' . $purchase_id . '-' . date('Ymd');
        }
    }

    // Handle builty file upload
    if (isset($_FILES['builty_file']) && $_FILES['builty_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['builty_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'builty_' . $purchase_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $updates[] = "builty_file = ?";
            $params[] = 'uploads/purchase/' . $filename;
            $updates[] = "builty_number = ?";
            $params[] = 'BIL-' . $purchase_id . '-' . date('Ymd');
        }
    }

    // Handle other documents upload
    if (isset($_FILES['other_file']) && $_FILES['other_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['other_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'other_' . $purchase_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $updates[] = "other_file = ?";
            $params[] = 'uploads/purchase/' . $filename;
        }
    }

    if (!empty($updates)) {
        $sql = "UPDATE purchase_main SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $purchase_id;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $response['success'] = true;
        $response['message'] = 'Documents uploaded successfully';
    } else {
        $response['success'] = false;
        $response['error'] = 'No files were uploaded';
    }

} catch (PDOException $e) {
    $response['success'] = false;
    $response['error'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>

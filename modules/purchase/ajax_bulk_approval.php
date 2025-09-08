<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['purchase_main_id'], $_POST['date'], $_POST['invoice_number'], $_POST['amount'], $_POST['builty_number'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$purchase_main_id = $_POST['purchase_main_id'];
$date = $_POST['date'];
$invoice_number = $_POST['invoice_number'];
$amount = $_POST['amount'];
$builty_number = $_POST['builty_number'];
$selected_items = $_POST['selected_items'] ?? [];

$bill_image = $_FILES['bill_image'] ?? null;
$builty_image = $_FILES['builty_image'] ?? null;

if (!$bill_image || $bill_image['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Bill image upload failed']);
    exit;
}

if (!$builty_image || $builty_image['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Builty image upload failed']);
    exit;
}

    // Validate file types and sizes
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($bill_image['type'], $allowed_types) || $bill_image['size'] > $max_file_size) {
        echo json_encode(['success' => false, 'error' => 'Invalid or too large bill image']);
        exit;
    }

    if (!in_array($builty_image['type'], $allowed_types) || $builty_image['size'] > $max_file_size) {
        echo json_encode(['success' => false, 'error' => 'Invalid or too large builty image']);
        exit;
    }

    // Save inside modules/purchase/uploads to match fetch paths
    $invoice_upload_dir = __DIR__ . '/uploads/invoice/';
    $builty_upload_dir = __DIR__ . '/uploads/builty/';

    if (!is_dir($invoice_upload_dir)) {
        mkdir($invoice_upload_dir, 0755, true);
    }
    if (!is_dir($builty_upload_dir)) {
        mkdir($builty_upload_dir, 0755, true);
    }

    // Sanitize original names to avoid spaces/special characters
    $sanitize = function($name) {
        $name = basename($name);
        return preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    };

    $bill_image_name = uniqid('invoice_') . '_' . $sanitize($bill_image['name']);
    $builty_image_name = uniqid('builty_') . '_' . $sanitize($builty_image['name']);

    $bill_image_path = $invoice_upload_dir . $bill_image_name;
    $builty_image_path = $builty_upload_dir . $builty_image_name;

if (!move_uploaded_file($bill_image['tmp_name'], $bill_image_path)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save invoice image']);
    exit;
}

if (!move_uploaded_file($builty_image['tmp_name'], $builty_image_path)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save builty image']);
    exit;
}

global $conn;

try {
    $conn->beginTransaction();
    
    if (empty($selected_items)) {
        // Update all items for this purchase
        $stmt = $conn->prepare("UPDATE purchase_items SET date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, updated_at = NOW() WHERE purchase_main_id = ?");
        $stmt->execute([
            $date,
            $invoice_number,
            $amount,
            $bill_image_name,
            $builty_number,
            $builty_image_name,
            $purchase_main_id
        ]);
    } else {
        // Update only selected items
        $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
        $stmt = $conn->prepare("UPDATE purchase_items SET date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, updated_at = NOW() WHERE id IN ($placeholders)");
        $params = array_merge([$date, $invoice_number, $amount, $bill_image_name, $builty_number, $builty_image_name], $selected_items);
        $stmt->execute($params);
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Bulk approval saved successfully']);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
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

if (!isset($_POST['item_id'], $_POST['date'], $_POST['invoice_number'], $_POST['amount'], $_POST['builty_number'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$item_id = $_POST['item_id'];
$date = $_POST['date'];
$invoice_number = $_POST['invoice_number'];
$amount = $_POST['amount'];
$builty_number = $_POST['builty_number'];

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
$builty_upload_dir = __DIR__ . '/uploads/Builty/';

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
$stmt = $conn->prepare("UPDATE purchase_items SET date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([
    $date,
    $invoice_number,
    $amount,
    $bill_image_name,
    $builty_number,
    $builty_image_name,
    $item_id
]);

echo json_encode(['success' => true, 'message' => 'Approval saved successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

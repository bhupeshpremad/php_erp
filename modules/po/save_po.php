<?php

include_once __DIR__ . '/../../config/config.php';
require_once ROOT_DIR_PATH . 'core/utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($conn) || !$conn instanceof PDO) {
    echo json_encode(['success' => false, 'message' => 'Database connection not initialized.']);
    exit;
}

try {
    $conn->beginTransaction();

    $po_id = $_POST['po_id'] ?? null;
    $edit_mode = ($po_id !== null && $po_id !== '');

    if ($edit_mode) {
        $stmt_check_lock = $conn->prepare("SELECT is_locked FROM po_main WHERE id = :po_id");
        $stmt_check_lock->bindValue(':po_id', $po_id, PDO::PARAM_INT);
        $stmt_check_lock->execute();
        $po_lock_status = $stmt_check_lock->fetch(PDO::FETCH_ASSOC);

        if ($po_lock_status && $po_lock_status['is_locked'] == 1) {
            throw new Exception("This Purchase Order is locked and cannot be updated.");
        }
    }

    $po_number = trim($_POST['po_number'] ?? '');
    $client_name = trim($_POST['client_name'] ?? '');
    $prepared_by = trim($_POST['prepared_by'] ?? '');
    $order_date = trim($_POST['order_date'] ?? '');
    $delivery_date = trim($_POST['delivery_date'] ?? '');

    if (empty($po_number) || empty($client_name) || empty($prepared_by) || empty($order_date) || empty($delivery_date)) {
        throw new Exception('Please fill all required main PO fields.');
    }

    if ($edit_mode) {
        $stmt_main = $conn->prepare("UPDATE po_main SET
            po_number = :po_number,
            client_name = :client_name,
            prepared_by = :prepared_by,
            order_date = :order_date,
            delivery_date = :delivery_date,
            updated_at = NOW()
            WHERE id = :po_id");

        $stmt_main->bindValue(':po_id', $po_id, PDO::PARAM_INT);

    } else {
        $stmt_main = $conn->prepare("INSERT INTO po_main (po_number, client_name, prepared_by, order_date, delivery_date, created_at, updated_at, status, is_locked) VALUES (
            :po_number, :client_name, :prepared_by, :order_date, :delivery_date, NOW(), NOW(), 'Pending', 0)");
    }

    $stmt_main->bindValue(':po_number', $po_number);
    $stmt_main->bindValue(':client_name', $client_name);
    $stmt_main->bindValue(':prepared_by', $prepared_by);
    $stmt_main->bindValue(':order_date', $order_date);
    $stmt_main->bindValue(':delivery_date', $delivery_date);
    $stmt_main->execute();

    if (!$edit_mode) {
        $po_id = $conn->lastInsertId();
    }

    $items_data = $_POST['items'] ?? [];

    if (empty($items_data)) {
        throw new Exception('At least one item detail is required.');
    }

    if ($edit_mode) {
        $stmt_delete_items = $conn->prepare("DELETE FROM po_items WHERE po_id = :po_id");
        $stmt_delete_items->bindValue(':po_id', $po_id, PDO::PARAM_INT);
        $stmt_delete_items->execute();
    }

    $stmt_insert_item = $conn->prepare("INSERT INTO po_items (po_id, product_code, product_name, quantity, price, total_amount, product_image, created_at, updated_at) VALUES (:po_id, :product_code, :product_name, :quantity, :price, :total_amount, :product_image, NOW(), NOW())");

    foreach ($items_data as $index => $item) {
        $product_code = trim($item['product_code'] ?? '');
        $product_name = trim($item['product_name'] ?? '');
        $quantity = filter_var($item['quantity'] ?? 0, FILTER_VALIDATE_FLOAT);
        $price = filter_var($item['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $total_amount = filter_var($item['total_amount'] ?? 0, FILTER_VALIDATE_FLOAT);

        if (empty($product_code) || empty($product_name) || $quantity === false || $quantity < 0 || $price === false || $price < 0 || $total_amount === false || $total_amount < 0) {
            throw new Exception("Invalid item data for row " . ($index + 1) . ". Please check all item fields.");
        }

        // Handle image upload - PHP converts items[0][product_image] to items array structure
        $product_image = '';
        if (isset($_FILES['items']['name'][$index]['product_image']) && !empty($_FILES['items']['name'][$index]['product_image'])) {
            $upload_dir = ROOT_DIR_PATH . 'uploads/po/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_name = basename($_FILES['items']['name'][$index]['product_image']);
            $file_tmp = $_FILES['items']['tmp_name'][$index]['product_image'];
            $file_size = $_FILES['items']['size'][$index]['product_image'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $max_file_size = 5 * 1024 * 1024; // 5 MB limit
            
            if (in_array($file_ext, $allowed_ext) && $file_size <= $max_file_size) {
                $new_file_name = uniqid() . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $product_image = $new_file_name;
                }
            } else {
                throw new Exception("Invalid or too large image for item " . ($index + 1) . ". Allowed types: JPG, PNG, GIF. Max size: 5MB.");
            }
        }

        $stmt_insert_item->bindValue(':po_id', $po_id, PDO::PARAM_INT);
        $stmt_insert_item->bindValue(':product_code', $product_code);
        $stmt_insert_item->bindValue(':product_name', $product_name);
        $stmt_insert_item->bindValue(':quantity', $quantity);
        $stmt_insert_item->bindValue(':price', $price);
        $stmt_insert_item->bindValue(':total_amount', $total_amount);
        $stmt_insert_item->bindValue(':product_image', $product_image);
        $stmt_insert_item->execute();
    }

    $conn->commit();

    if (!$edit_mode) {
        echo json_encode(['success' => true, 'message' => 'Purchase Order saved successfully.', 'po_id' => $po_id]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Purchase Order updated successfully.']);
    }

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error saving Purchase Order: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error saving Purchase Order: ' . $e->getMessage()]);
}
exit;
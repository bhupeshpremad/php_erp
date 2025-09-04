<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Change to receive data from $_POST and $_FILES
$po_number = $_POST['po_number'] ?? null;
$jci_number = $_POST['jci_number'] ?? null;
$sell_order_number = $_POST['sell_order_number'] ?? null;
$bom_number = $_POST['bom_number'] ?? null;
$items_json = $_POST['items_json'] ?? '[]';
$is_superadmin = filter_var($_POST['is_superadmin'] ?? false, FILTER_VALIDATE_BOOLEAN);
$items = json_decode($items_json, true); // Decode the JSON string back into an array

if (!$po_number || !$jci_number || !$sell_order_number || !$bom_number || empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

global $conn;

// Define upload directories
$invoice_upload_dir = ROOT_DIR_PATH . 'modules/purchase/uploads/invoice/';
$builty_upload_dir = ROOT_DIR_PATH . 'modules/purchase/uploads/Builty/';

// Ensure upload directories exist
if (!is_dir($invoice_upload_dir)) { mkdir($invoice_upload_dir, 0777, true); }
if (!is_dir($builty_upload_dir)) { mkdir($builty_upload_dir, 0777, true); }

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if purchase already exists for this JCI
    $stmt_check = $conn->prepare("SELECT id FROM purchase_main WHERE jci_number = ?");
    $stmt_check->execute([$jci_number]);
    $existing_purchase = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_purchase) {
        // Update existing purchase
        $purchase_main_id = $existing_purchase['id'];
        $stmt_update = $conn->prepare("UPDATE purchase_main SET po_number = ?, sell_order_number = ?, bom_number = ?, updated_at = NOW() WHERE id = ?");
        $stmt_update->execute([$po_number, $sell_order_number, $bom_number, $purchase_main_id]);
        
        // Delete only non-approved items to preserve approved ones
        // An item is considered 'approved' if it has an invoice_number
        $stmt_delete = $conn->prepare("DELETE FROM purchase_items WHERE purchase_main_id = ? AND (invoice_number IS NULL OR invoice_number = '')");
        $stmt_delete->execute([$purchase_main_id]);
    } else {
        // Insert new purchase_main
        $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number]);
        $purchase_main_id = $conn->lastInsertId();
    }

    // Insert/Update purchase_items
    $stmt_check_item = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_type = ? AND product_name = ? AND job_card_number = ?");
    $stmt_insert_item = $conn->prepare("INSERT INTO purchase_items (purchase_main_id, supplier_name, product_type, product_name, job_card_number, assigned_quantity, price, total, date, invoice_number, amount, invoice_image, builty_number, builty_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Define update statements based on superadmin status
    $stmt_update_item_superadmin = $conn->prepare("UPDATE purchase_items SET assigned_quantity = ?, price = ?, total = ?, date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, updated_at = NOW() WHERE id = ?");
    $stmt_update_item_regular = $conn->prepare("UPDATE purchase_items SET assigned_quantity = ?, price = ?, total = ?, date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, updated_at = NOW() WHERE id = ? AND (invoice_number IS NULL OR invoice_number = '')");
    $stmt_update_image_only = $conn->prepare("UPDATE purchase_items SET invoice_image = ?, builty_image = ?, updated_at = NOW() WHERE id = ?");

    foreach ($items as $item_index => $item_data) {
        $supplier_name = $item_data['supplier_name'] ?? '';
        $product_type = $item_data['product_type'] ?? '';
        $product_name = $item_data['product_name'] ?? '';
        $job_card_number = $item_data['job_card_number'] ?? '';
        $assigned_quantity = floatval($item_data['assigned_quantity'] ?? 0);
        $price = floatval($item_data['price'] ?? 0);
        $total = $assigned_quantity * $price;
        $date = $item_data['date'] ?? null; // Assuming date can be passed from frontend
        $invoice_number = $item_data['invoice_number'] ?? null;
        $builty_number = $item_data['builty_number'] ?? null;

        $existing_invoice_image = $item_data['existing_invoice_image'] ?? null;
        $existing_builty_image = $item_data['existing_builty_image'] ?? null;

        $invoice_image_name = $existing_invoice_image;
        $builty_image_name = $existing_builty_image;

        // Handle invoice image upload
        if (isset($_FILES['invoice_image_' . $item_index]) && $_FILES['invoice_image_' . $item_index]['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['invoice_image_' . $item_index]['tmp_name'];
            $file_extension = pathinfo($_FILES['invoice_image_' . $item_index]['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('invoice_') . '.' . $file_extension;
            $dest_path = $invoice_upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $invoice_image_name = $new_file_name;
            }
        }

        // Handle builty image upload
        if (isset($_FILES['builty_image_' . $item_index]) && $_FILES['builty_image_' . $item_index]['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['builty_image_' . $item_index]['tmp_name'];
            $file_extension = pathinfo($_FILES['builty_image_' . $item_index]['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('builty_') . '.' . $file_extension;
            $dest_path = $builty_upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $builty_image_name = $new_file_name;
            }
        }
        
        // Check if item already exists for update or insert
        $stmt_check_item->execute([
            $purchase_main_id,
            $supplier_name,
            $product_type,
            $product_name,
            $job_card_number
        ]);
        $existing_item = $stmt_check_item->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            if ($is_superadmin) {
                // Superadmin can update all fields
                $stmt_update_item_superadmin->execute([
                    $assigned_quantity,
                    $price,
                    $total,
                    $date,
                    $invoice_number,
                    $assigned_quantity * $price, // Assuming amount is total
                    $invoice_image_name,
                    $builty_number,
                    $builty_image_name,
                    $existing_item['id']
                ]);
            } else {
                // Regular user: Update if not approved, otherwise only images if changed
                if (empty($existing_item['invoice_number'])) {
                    $stmt_update_item_regular->execute([
                        $assigned_quantity,
                        $price,
                        $total,
                        $date,
                        $invoice_number,
                        $assigned_quantity * $price, // Assuming amount is total
                        $invoice_image_name,
                        $builty_number,
                        $builty_image_name,
                        $existing_item['id']
                    ]);
                } else {
                    // If approved, update images only if new files are uploaded
                    if ($invoice_image_name !== $existing_item['invoice_image'] || $builty_image_name !== $existing_item['builty_image']) {
                        $stmt_update_image_only->execute([$invoice_image_name, $builty_image_name, $existing_item['id']]);
                    }
                }
            }
        } else {
            // Insert new item
            $stmt_insert_item->execute([
                $purchase_main_id,
                $supplier_name,
                $product_type,
                $product_name,
                $job_card_number,
                $assigned_quantity,
                $price,
                $total,
                $date,
                $invoice_number,
                $assigned_quantity * $price, // Assuming amount is total
                $invoice_image_name,
                $builty_number,
                $builty_image_name
            ]);
        }
    }

    // Update JCI main table to mark purchase as created
    $stmt_update_jci = $conn->prepare("UPDATE jci_main SET purchase_created = 1 WHERE jci_number = ?");
    $stmt_update_jci->execute([$jci_number]);

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Purchase saved successfully']);
    exit;

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Application error: ' . $e->getMessage()]);
    exit;
}
?>

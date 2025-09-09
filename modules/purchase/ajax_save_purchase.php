<?php
session_start();
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
        // Check if created_by column exists
        $has_created_by = false;
        try {
            $stmt_check = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
            $has_created_by = $stmt_check->rowCount() > 0;
        } catch (Exception $e) {
            $has_created_by = false;
        }
        
        if ($has_created_by) {
            // Get current user ID
            $created_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1;
            // Insert new purchase_main with created_by
            $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number, $created_by]);
        } else {
            // Insert new purchase_main without created_by
            $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number]);
        }
        $purchase_main_id = $conn->lastInsertId();
    }

    // Insert/Update purchase_items
    $stmt_check_item = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_type = ? AND product_name = ? AND job_card_number = ? AND assigned_quantity = ? AND price = ?");
    $stmt_precise_match = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_type = ? AND product_name = ? AND job_card_number = ? AND assigned_quantity = ? AND price = ? LIMIT 1");
    $stmt_insert_item = $conn->prepare("INSERT INTO purchase_items (purchase_main_id, supplier_name, product_type, product_name, job_card_number, assigned_quantity, price, total, date, invoice_number, amount, invoice_image, builty_number, builty_image, length_ft, width_ft, thickness_inch, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Define update statements based on superadmin status
    $stmt_update_item_superadmin = $conn->prepare("UPDATE purchase_items SET assigned_quantity = ?, price = ?, total = ?, date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() WHERE id = ?");
    $stmt_update_item_regular = $conn->prepare("UPDATE purchase_items SET assigned_quantity = ?, price = ?, total = ?, date = ?, invoice_number = ?, amount = ?, invoice_image = ?, builty_number = ?, builty_image = ?, length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() WHERE id = ? AND (invoice_number IS NULL OR invoice_number = '')");
    $stmt_update_image_only = $conn->prepare("UPDATE purchase_items SET invoice_image = ?, builty_image = ?, updated_at = NOW() WHERE id = ?");

    foreach ($items as $item_index => $item_data) {
        $supplier_name = $item_data['supplier_name'] ?? '';
        $product_type = $item_data['product_type'] ?? '';
        $product_name = $item_data['product_name'] ?? '';
        $job_card_number = $item_data['job_card_number'] ?? '';
        $assigned_quantity = floatval($item_data['assigned_quantity'] ?? 0);
        $price = floatval($item_data['price'] ?? 0);
        $total = $assigned_quantity * $price;
        $date = $item_data['date'] ?? null;
        $invoice_number = $item_data['invoice_number'] ?? null;
        $builty_number = $item_data['builty_number'] ?? null;
        $bom_quantity = floatval($item_data['bom_quantity'] ?? 0);
        $unique_id = $item_data['uniqueId'] ?? null;
        $length_ft = floatval($item_data['length_ft'] ?? 0);
        $width_ft = floatval($item_data['width_ft'] ?? 0);
        $thickness_inch = floatval($item_data['thickness_inch'] ?? 0);

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
        
        // Debug logging
        error_log("Processing item: supplier={$supplier_name}, product={$product_name}, job_card={$job_card_number}, assigned_qty={$assigned_quantity}, price={$price}, bom_qty={$bom_quantity}, unique_id={$unique_id}");
        
        // Check if item already exists - use unique ID if provided, otherwise use precise matching
        if (!empty($unique_id)) {
            // Use unique ID for precise row identification
            $stmt_unique = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND id = ?");
            $stmt_unique->execute([$purchase_main_id, $unique_id]);
            $existing_item = $stmt_unique->fetch(PDO::FETCH_ASSOC);
            error_log("Unique ID match result: " . ($existing_item ? "Found ID {$existing_item['id']}" : "Not found"));
        } else {
            // For individual saves, use BOM quantity + other fields for precise matching
            $bom_quantity = floatval($item_data['bom_quantity'] ?? 0);
            $row_serial = $item_data['row_serial'] ?? null;
            
            error_log("BOM quantity matching: bom_qty={$bom_quantity}, row_serial={$row_serial}");
            
            if ($bom_quantity > 0) {
                // Use BOM quantity for precise row identification
                $stmt_bom_match = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND supplier_name = ? AND product_type = ? AND product_name = ? AND job_card_number = ? AND price = ? AND (assigned_quantity = ? OR ABS(assigned_quantity - ?) < 0.001) LIMIT 1");
                $stmt_bom_match->execute([
                    $purchase_main_id,
                    $supplier_name,
                    $product_type,
                    $product_name,
                    $job_card_number,
                    $price,
                    $assigned_quantity,
                    $assigned_quantity
                ]);
                $existing_item = $stmt_bom_match->fetch(PDO::FETCH_ASSOC);
                error_log("BOM match result: " . ($existing_item ? "Found ID {$existing_item['id']}" : "Not found"));
            } else {
                // Fallback to basic matching
                $stmt_check_item->execute([
                    $purchase_main_id,
                    $supplier_name,
                    $product_type,
                    $product_name,
                    $job_card_number,
                    $assigned_quantity,
                    $price
                ]);
                $existing_item = $stmt_check_item->fetch(PDO::FETCH_ASSOC);
                error_log("Basic match result: " . ($existing_item ? "Found ID {$existing_item['id']}" : "Not found"));
            }
        }

        if ($existing_item) {
            error_log("Updating existing item ID: {$existing_item['id']}");
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
                    $length_ft,
                    $width_ft,
                    $thickness_inch,
                    $existing_item['id']
                ]);
                error_log("Superadmin update completed for ID: {$existing_item['id']}");
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
                        $length_ft,
                        $width_ft,
                        $thickness_inch,
                        $existing_item['id']
                    ]);
                    error_log("Regular user update completed for ID: {$existing_item['id']}");
                } else {
                    // If approved, update images only if new files are uploaded
                    if ($invoice_image_name !== $existing_item['invoice_image'] || $builty_image_name !== $existing_item['builty_image']) {
                        $stmt_update_image_only->execute([$invoice_image_name, $builty_image_name, $existing_item['id']]);
                        error_log("Image-only update completed for ID: {$existing_item['id']}");
                    } else {
                        error_log("No update needed for approved item ID: {$existing_item['id']}");
                    }
                }
            }
        } else {
            error_log("Inserting new item for supplier: {$supplier_name}, product: {$product_name}");
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
                $builty_image_name,
                $length_ft,
                $width_ft,
                $thickness_inch
            ]);
            $new_id = $conn->lastInsertId();
            error_log("New item inserted with ID: {$new_id}");
        }
    }

    // Update JCI main table to mark purchase as created
    $stmt_update_jci = $conn->prepare("UPDATE jci_main SET purchase_created = 1 WHERE jci_number = ?");
    $stmt_update_jci->execute([$jci_number]);

    // Commit transaction
    $conn->commit();

    error_log("Purchase save completed successfully for JCI: {$jci_number}");
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

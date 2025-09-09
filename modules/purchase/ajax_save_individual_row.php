<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/audit_log.php';

header('Content-Type: application/json');

// Individual row save - simplified approach with input validation
$po_number = filter_var($_POST['po_number'] ?? null, FILTER_SANITIZE_STRING);
$jci_number = filter_var($_POST['jci_number'] ?? null, FILTER_SANITIZE_STRING);
$sell_order_number = filter_var($_POST['sell_order_number'] ?? null, FILTER_SANITIZE_STRING);
$bom_number = filter_var($_POST['bom_number'] ?? null, FILTER_SANITIZE_STRING);
$items_json = $_POST['items_json'] ?? '[]';
$is_superadmin = filter_var($_POST['is_superadmin'] ?? false, FILTER_VALIDATE_BOOLEAN);

// Validate JSON
$items = json_decode($items_json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

if (!$po_number || !$jci_number || !$sell_order_number || !$bom_number || empty($items) || count($items) !== 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid data for individual row save']);
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

    // Get or create purchase_main record
    $stmt_check = $conn->prepare("SELECT id FROM purchase_main WHERE jci_number = ?");
    $stmt_check->execute([$jci_number]);
    $existing_purchase = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_purchase) {
        $purchase_main_id = $existing_purchase['id'];
        // Update existing purchase
        $stmt_update = $conn->prepare("UPDATE purchase_main SET po_number = ?, sell_order_number = ?, bom_number = ?, updated_at = NOW() WHERE id = ?");
        $stmt_update->execute([$po_number, $sell_order_number, $bom_number, $purchase_main_id]);
    } else {
        // Create new purchase_main
        $has_created_by = false;
        try {
            $stmt_check = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
            $has_created_by = $stmt_check->rowCount() > 0;
        } catch (Exception $e) {
            $has_created_by = false;
        }
        
        if ($has_created_by) {
            $created_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1;
            $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number, $created_by]);
        } else {
            $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number]);
        }
        $purchase_main_id = $conn->lastInsertId();
    }

    // Process the single item with sanitization
    $item_data = $items[0];
    $supplier_name = filter_var($item_data['supplier_name'] ?? '', FILTER_SANITIZE_STRING);
    $product_type = filter_var($item_data['product_type'] ?? '', FILTER_SANITIZE_STRING);
    $product_name = filter_var($item_data['product_name'] ?? '', FILTER_SANITIZE_STRING);
    $job_card_number = filter_var($item_data['job_card_number'] ?? '', FILTER_SANITIZE_STRING);
    $assigned_quantity = filter_var($item_data['assigned_quantity'] ?? 0, FILTER_VALIDATE_FLOAT);
    $price = filter_var($item_data['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $total = $assigned_quantity * $price;
    $date = $item_data['date'] ?? null;
    $invoice_number = filter_var($item_data['invoice_number'] ?? '', FILTER_SANITIZE_STRING);
    $builty_number = filter_var($item_data['builty_number'] ?? '', FILTER_SANITIZE_STRING);
    $bom_quantity = filter_var($item_data['bom_quantity'] ?? 0, FILTER_VALIDATE_FLOAT);
    $unique_id = filter_var($item_data['uniqueId'] ?? null, FILTER_VALIDATE_INT);
    
    // Validate required fields
    if (empty($supplier_name) || $assigned_quantity <= 0 || $price < 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid input data']);
        exit;
    }

    $existing_invoice_image = $item_data['existing_invoice_image'] ?? null;
    $existing_builty_image = $item_data['existing_builty_image'] ?? null;

    $invoice_image_name = $existing_invoice_image;
    $builty_image_name = $existing_builty_image;

    // Handle file uploads with security validation
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    if (isset($_FILES['invoice_image_0']) && $_FILES['invoice_image_0']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['invoice_image_0']['tmp_name'];
        $file_size = $_FILES['invoice_image_0']['size'];
        $file_extension = strtolower(pathinfo($_FILES['invoice_image_0']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $file_size <= $max_file_size) {
            $new_file_name = uniqid('invoice_') . '.' . $file_extension;
            $dest_path = $invoice_upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $invoice_image_name = $new_file_name;
            }
        }
    }

    if (isset($_FILES['builty_image_0']) && $_FILES['builty_image_0']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['builty_image_0']['tmp_name'];
        $file_size = $_FILES['builty_image_0']['size'];
        $file_extension = strtolower(pathinfo($_FILES['builty_image_0']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions) && $file_size <= $max_file_size) {
            $new_file_name = uniqid('builty_') . '.' . $file_extension;
            $dest_path = $builty_upload_dir . $new_file_name;
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $builty_image_name = $new_file_name;
            }
        }
    }

    // Get additional dimensions for Wood items
    $length_ft = floatval($item_data['length_ft'] ?? 0);
    $width_ft = floatval($item_data['width_ft'] ?? 0);
    $thickness_inch = floatval($item_data['thickness_inch'] ?? 0);
    
    // Find existing item using multiple criteria for precision
    $existing_item = null;
    
    if (!empty($unique_id)) {
        // Use unique ID if provided
        $stmt_unique = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND id = ?");
        $stmt_unique->execute([$purchase_main_id, $unique_id]);
        $existing_item = $stmt_unique->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$existing_item) {
        // For Wood items, include dimensions in matching
        if ($product_type === 'Wood' && $length_ft > 0 && $width_ft > 0) {
            $stmt_wood = $conn->prepare("
                SELECT id, invoice_number, builty_number, invoice_image, builty_image 
                FROM purchase_items 
                WHERE purchase_main_id = ? 
                AND supplier_name = ? 
                AND product_type = ? 
                AND product_name = ? 
                AND job_card_number = ? 
                AND ABS(price - ?) < 0.01
                AND ABS(assigned_quantity - ?) < 0.001
                AND ABS(COALESCE(length_ft, 0) - ?) < 0.01
                AND ABS(COALESCE(width_ft, 0) - ?) < 0.01
                AND ABS(COALESCE(thickness_inch, 0) - ?) < 0.01
                LIMIT 1
            ");
            $stmt_wood->execute([
                $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                $price, $assigned_quantity, $length_ft, $width_ft, $thickness_inch
            ]);
            $existing_item = $stmt_wood->fetch(PDO::FETCH_ASSOC);
        } else {
            // For non-Wood items, use standard matching
            $stmt_precise = $conn->prepare("
                SELECT id, invoice_number, builty_number, invoice_image, builty_image 
                FROM purchase_items 
                WHERE purchase_main_id = ? 
                AND supplier_name = ? 
                AND product_type = ? 
                AND product_name = ? 
                AND job_card_number = ? 
                AND ABS(price - ?) < 0.01
                AND ABS(assigned_quantity - ?) < 0.001
                LIMIT 1
            ");
            $stmt_precise->execute([
                $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                $price, $assigned_quantity
            ]);
            $existing_item = $stmt_precise->fetch(PDO::FETCH_ASSOC);
        }
    }

    if ($existing_item) {
        // Update existing item
        if ($is_superadmin) {
            // Superadmin can update all fields including dimensions
            if ($product_type === 'Wood') {
                $stmt_update = $conn->prepare("
                    UPDATE purchase_items SET 
                    assigned_quantity = ?, price = ?, total = ?, date = ?, 
                    invoice_number = ?, amount = ?, invoice_image = ?, 
                    builty_number = ?, builty_image = ?, 
                    length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt_update->execute([
                    $assigned_quantity, $price, $total, $date,
                    $invoice_number, $total, $invoice_image_name,
                    $builty_number, $builty_image_name,
                    $length_ft, $width_ft, $thickness_inch, $existing_item['id']
                ]);
            } else {
                $stmt_update = $conn->prepare("
                    UPDATE purchase_items SET 
                    assigned_quantity = ?, price = ?, total = ?, date = ?, 
                    invoice_number = ?, amount = ?, invoice_image = ?, 
                    builty_number = ?, builty_image = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt_update->execute([
                    $assigned_quantity, $price, $total, $date,
                    $invoice_number, $total, $invoice_image_name,
                    $builty_number, $builty_image_name, $existing_item['id']
                ]);
            }
        } else {
            // Regular user: Update if not approved
            if (empty($existing_item['invoice_number'])) {
                if ($product_type === 'Wood') {
                    $stmt_update = $conn->prepare("
                        UPDATE purchase_items SET 
                        assigned_quantity = ?, price = ?, total = ?, date = ?, 
                        invoice_number = ?, amount = ?, invoice_image = ?, 
                        builty_number = ?, builty_image = ?, 
                        length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt_update->execute([
                        $assigned_quantity, $price, $total, $date,
                        $invoice_number, $total, $invoice_image_name,
                        $builty_number, $builty_image_name,
                        $length_ft, $width_ft, $thickness_inch, $existing_item['id']
                    ]);
                } else {
                    $stmt_update = $conn->prepare("
                        UPDATE purchase_items SET 
                        assigned_quantity = ?, price = ?, total = ?, date = ?, 
                        invoice_number = ?, amount = ?, invoice_image = ?, 
                        builty_number = ?, builty_image = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt_update->execute([
                        $assigned_quantity, $price, $total, $date,
                        $invoice_number, $total, $invoice_image_name,
                        $builty_number, $builty_image_name, $existing_item['id']
                    ]);
                }
            } else {
                // Only update images if new files uploaded
                if ($invoice_image_name !== $existing_item['invoice_image'] || $builty_image_name !== $existing_item['builty_image']) {
                    $stmt_update = $conn->prepare("UPDATE purchase_items SET invoice_image = ?, builty_image = ?, updated_at = NOW() WHERE id = ?");
                    $stmt_update->execute([$invoice_image_name, $builty_image_name, $existing_item['id']]);
                }
            }
        }
        $updated_id = $existing_item['id'];
    } else {
        // Insert new item
        if ($product_type === 'Wood') {
            $stmt_insert = $conn->prepare("
                INSERT INTO purchase_items 
                (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
                 assigned_quantity, price, total, date, invoice_number, amount, 
                 invoice_image, builty_number, builty_image, 
                 length_ft, width_ft, thickness_inch, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt_insert->execute([
                $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                $invoice_image_name, $builty_number, $builty_image_name,
                $length_ft, $width_ft, $thickness_inch
            ]);
        } else {
            $stmt_insert = $conn->prepare("
                INSERT INTO purchase_items 
                (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
                 assigned_quantity, price, total, date, invoice_number, amount, 
                 invoice_image, builty_number, builty_image, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt_insert->execute([
                $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                $invoice_image_name, $builty_number, $builty_image_name
            ]);
        }
        $updated_id = $conn->lastInsertId();
    }

    // Update JCI main table
    $stmt_update_jci = $conn->prepare("UPDATE jci_main SET purchase_created = 1 WHERE jci_number = ?");
    $stmt_update_jci->execute([$jci_number]);

    // Log the action
    logPurchaseAction(
        $existing_item ? 'individual_row_updated' : 'individual_row_created',
        $jci_number,
        [
            'supplier_name' => $supplier_name,
            'product_name' => $product_name,
            'job_card_number' => $job_card_number,
            'assigned_quantity' => $assigned_quantity,
            'price' => $price,
            'updated_id' => $updated_id
        ]
    );
    
    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Individual row saved successfully',
        'updated_id' => $updated_id,
        'action' => $existing_item ? 'updated' : 'inserted'
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Application error: ' . $e->getMessage()]);
}
?>
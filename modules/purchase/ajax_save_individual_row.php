<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
$__security_inc = __DIR__ . '/../../include/core/security_inc.php';
if (file_exists($__security_inc)) {
    include_once $__security_inc;
}

// Try multiple config paths
$config_paths = [
    __DIR__ . '/../../config/config.php',
    dirname(__DIR__, 2) . '/config/config.php',
    $_SERVER['DOCUMENT_ROOT'] . '/config/config.php'
];

$config_loaded = false;
foreach ($config_paths as $path) {
    if (file_exists($path)) {
        include_once $path;
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    echo json_encode(['success' => false, 'error' => 'Config file not found']);
    exit;
}

// include_once __DIR__ . '/audit_log.php'; // Disabled - file not found

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

if (!$po_number || !$jci_number || !$sell_order_number || !$bom_number || empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data for individual row save']);
    exit;
}

// Use global connection if available, otherwise create new
if (!isset($conn)) {
    $host = DB_HOST;
    $dbname = DB_NAME;
    $username = DB_USER;
    $password = DB_PASS;
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Define upload directories to align with frontend expectations
$invoice_upload_dir = __DIR__ . '/uploads/invoice/';
$builty_upload_dir = __DIR__ . '/uploads/Builty/';

// Ensure upload directories exist
if (!is_dir($invoice_upload_dir)) { mkdir($invoice_upload_dir, 0777, true); }
if (!is_dir($builty_upload_dir)) { mkdir($builty_upload_dir, 0777, true); }

$response = ['success' => false, 'error' => 'An unknown error occurred.'];

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
        // Create new purchase_main (support schemas without created_by)
        $created_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;

        // Detect if created_by column exists
        $has_created_by = false;
        try {
            $stmt_col = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
            $has_created_by = $stmt_col->rowCount() > 0;
        } catch (Exception $e) {
            $has_created_by = false;
        }

        if ($has_created_by) {
            $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number, $created_by ?? 0]);
        } else {
            $stmt_main = $conn->prepare("INSERT INTO purchase_main (po_number, jci_number, sell_order_number, bom_number, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt_main->execute([$po_number, $jci_number, $sell_order_number, $bom_number]);
        }
        $purchase_main_id = $conn->lastInsertId();
    }

    // Loop through each item and process it
    foreach ($items as $index => $item_data) {
        $supplier_name = filter_var($item_data['supplier_name'] ?? '', FILTER_SANITIZE_STRING);
        $product_type = filter_var($item_data['product_type'] ?? '', FILTER_SANITIZE_STRING);
        $product_name = filter_var($item_data['product_name'] ?? '', FILTER_SANITIZE_STRING);
        $job_card_number = filter_var($item_data['job_card_number'] ?? '', FILTER_SANITIZE_STRING);
        $assigned_quantity = filter_var($item_data['assigned_quantity'] ?? 0, FILTER_VALIDATE_FLOAT);
        $price = filter_var($item_data['price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $total = $assigned_quantity * $price;
        $date = $item_data['date'] ?? date('Y-m-d');
        $invoice_number = filter_var($item_data['invoice_number'] ?? '', FILTER_SANITIZE_STRING);
        $builty_number = filter_var($item_data['builty_number'] ?? '', FILTER_SANITIZE_STRING);
        $row_id = filter_var($item_data['row_id'] ?? null, FILTER_VALIDATE_INT);
        $rowIndex = filter_var($item_data['rowIndex'] ?? null, FILTER_VALIDATE_INT);

        if ($row_id === null || $rowIndex === null) {
            continue; // Skip if no row_id
        }

        // Validate required fields
        if (empty($supplier_name) || $assigned_quantity <= 0 || $price < 0) {
            continue; // Skip empty rows
        }

        $existing_invoice_image = $item_data['existing_invoice_image'] ?? null;
        $existing_builty_image = $item_data['existing_builty_image'] ?? null;

        $invoice_image_name = $existing_invoice_image;
        $builty_image_name = $existing_builty_image;

        // Handle file uploads with security validation
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        // Prefer row-id based file key for uniqueness; fallback to legacy rowIndex-based key
        $file_key_invoice_row = 'invoice_image_row_' . $row_id;
        $file_key_invoice_legacy = 'invoice_image_' . $rowIndex;
        $invoice_file_key = null;
        if (isset($_FILES[$file_key_invoice_row]) && $_FILES[$file_key_invoice_row]['error'] === UPLOAD_ERR_OK) {
            $invoice_file_key = $file_key_invoice_row;
        } elseif (isset($_FILES[$file_key_invoice_legacy]) && $_FILES[$file_key_invoice_legacy]['error'] === UPLOAD_ERR_OK) {
            $invoice_file_key = $file_key_invoice_legacy;
        }
        if ($invoice_file_key) {
            $file_tmp_path = $_FILES[$invoice_file_key]['tmp_name'];
            $file_size = $_FILES[$invoice_file_key]['size'];
            $file_ext = strtolower(pathinfo($_FILES[$invoice_file_key]['name'], PATHINFO_EXTENSION));

            if ($file_size <= $max_file_size && in_array($file_ext, $allowed_extensions)) {
                $invoice_image_name = uniqid('inv_', true) . '.' . $file_ext;
                move_uploaded_file($file_tmp_path, $invoice_upload_dir . $invoice_image_name);
            }
        }

        // Prefer row-id based file key for uniqueness; fallback to legacy rowIndex-based key
        $file_key_builty_row = 'builty_image_row_' . $row_id;
        $file_key_builty_legacy = 'builty_image_' . $rowIndex;
        $builty_file_key = null;
        if (isset($_FILES[$file_key_builty_row]) && $_FILES[$file_key_builty_row]['error'] === UPLOAD_ERR_OK) {
            $builty_file_key = $file_key_builty_row;
        } elseif (isset($_FILES[$file_key_builty_legacy]) && $_FILES[$file_key_builty_legacy]['error'] === UPLOAD_ERR_OK) {
            $builty_file_key = $file_key_builty_legacy;
        }
        if ($builty_file_key) {
            $file_tmp_path = $_FILES[$builty_file_key]['tmp_name'];
            $file_size = $_FILES[$builty_file_key]['size'];
            $file_ext = strtolower(pathinfo($_FILES[$builty_file_key]['name'], PATHINFO_EXTENSION));

            if ($file_size <= $max_file_size && in_array($file_ext, $allowed_extensions)) {
                $builty_image_name = uniqid('blt_', true) . '.' . $file_ext;
                move_uploaded_file($file_tmp_path, $builty_upload_dir . $builty_image_name);
            }
        }

        // Check if an item for this row_id already exists
        $existing_item = null;
        $stmt_find = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND row_id = ? AND job_card_number = ? LIMIT 1");
        $stmt_find->execute([$purchase_main_id, $row_id, $job_card_number]);
        $existing_item = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Update existing item
            if ($is_superadmin) {
                if ($product_type === 'Wood') {
                    $stmt_update = $conn->prepare("
                        UPDATE purchase_items SET 
                        supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
                        assigned_quantity = ?, price = ?, total = ?, date = ?, 
                        invoice_number = ?, amount = ?, invoice_image = ?, 
                        builty_number = ?, builty_image = ?, 
                        length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt_update->execute([
                        $supplier_name, $product_type, $product_name, $job_card_number,
                        $assigned_quantity, $price, $total, $date,
                        $invoice_number, $total, $invoice_image_name,
                        $builty_number, $builty_image_name,
                        floatval($item_data['length_ft'] ?? 0),
                        floatval($item_data['width_ft'] ?? 0),
                        floatval($item_data['thickness_inch'] ?? 0),
                        $existing_item['id']
                    ]);
                } else {
                    $stmt_update = $conn->prepare("
                        UPDATE purchase_items SET 
                        supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
                        assigned_quantity = ?, price = ?, total = ?, date = ?, 
                        invoice_number = ?, amount = ?, invoice_image = ?, 
                        builty_number = ?, builty_image = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt_update->execute([
                        $supplier_name, $product_type, $product_name, $job_card_number,
                        $assigned_quantity, $price, $total, $date,
                        $invoice_number, $total, $invoice_image_name,
                        $builty_number, $builty_image_name,
                        $existing_item['id']
                    ]);
                }
            } else {
                if (empty($existing_item['invoice_number'])) {
                    if ($product_type === 'Wood') {
                        $stmt_update = $conn->prepare("
                            UPDATE purchase_items SET 
                            supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
                            assigned_quantity = ?, price = ?, total = ?, date = ?, 
                            invoice_number = ?, amount = ?, invoice_image = ?, 
                            builty_number = ?, builty_image = ?, 
                            length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt_update->execute([
                            $supplier_name, $product_type, $product_name, $job_card_number,
                            $assigned_quantity, $price, $total, $date,
                            $invoice_number, $total, $invoice_image_name,
                            $builty_number, $builty_image_name,
                            floatval($item_data['length_ft'] ?? 0),
                            floatval($item_data['width_ft'] ?? 0),
                            floatval($item_data['thickness_inch'] ?? 0),
                            $existing_item['id']
                        ]);
                    } else {
                        $stmt_update = $conn->prepare("
                            UPDATE purchase_items SET 
                            supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
                            assigned_quantity = ?, price = ?, total = ?, date = ?, 
                            invoice_number = ?, amount = ?, invoice_image = ?, 
                            builty_number = ?, builty_image = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt_update->execute([
                            $supplier_name, $product_type, $product_name, $job_card_number,
                            $assigned_quantity, $price, $total, $date,
                            $invoice_number, $total, $invoice_image_name,
                            $builty_number, $builty_image_name,
                            $existing_item['id']
                        ]);
                    }
                }
            }
            $updated_id = $existing_item['id'];
        } else {
            // Insert new item with row_id
            if ($product_type === 'Wood') {
                $stmt_insert = $conn->prepare("
                    INSERT INTO purchase_items 
                    (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
                     assigned_quantity, price, total, date, invoice_number, amount, 
                     invoice_image, builty_number, builty_image, 
                     length_ft, width_ft, thickness_inch, row_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt_insert->execute([
                    $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                    $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                    $invoice_image_name, $builty_number, $builty_image_name,
                    floatval($item_data['length_ft'] ?? 0),
                    floatval($item_data['width_ft'] ?? 0),
                    floatval($item_data['thickness_inch'] ?? 0),
                    $row_id
                ]);
            } else {
                $stmt_insert = $conn->prepare("
                    INSERT INTO purchase_items 
                    (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
                     assigned_quantity, price, total, date, invoice_number, amount, 
                     invoice_image, builty_number, builty_image, row_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt_insert->execute([
                    $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                    $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                    $invoice_image_name, $builty_number, $builty_image_name, $row_id
                ]);
            }
            $updated_id = $conn->lastInsertId();
        }
    }

    // Update JCI main table
    $stmt_update_jci = $conn->prepare("UPDATE jci_main SET purchase_created = 1 WHERE jci_number = ?");
    $stmt_update_jci->execute([$jci_number]);

    // Log the action (disabled - audit_log.php not found)
    // logPurchaseAction(
    //     $existing_item ? 'individual_row_updated' : 'individual_row_created',
    //     $jci_number,
    //     [
    //         'supplier_name' => $supplier_name,
    //         'product_name' => $product_name,
    //         'job_card_number' => $job_card_number,
    //         'assigned_quantity' => $assigned_quantity,
    //         'price' => $price,
    //         'updated_id' => $updated_id
    //     ]
    // );
    
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
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

// Use global connection
global $conn;
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

// Define upload directories to align with frontend expectations
$invoice_upload_dir = __DIR__ . '/uploads/invoice/';
$builty_upload_dir = __DIR__ . '/uploads/Builty/';

// Ensure upload directories exist
if (!is_dir($invoice_upload_dir)) { mkdir($invoice_upload_dir, 0777, true); }
if (!is_dir($builty_upload_dir)) { mkdir($builty_upload_dir, 0777, true); }

$response = ['success' => false, 'error' => 'An unknown error occurred.'];

try {
    // Start transaction with optimized isolation level
    $conn->beginTransaction();
    
    // Optimize connection for faster writes
    $conn->exec("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
    $conn->exec("SET SESSION autocommit = 0");

    // Get or create purchase_main record with FOR UPDATE lock
    $stmt_check = $conn->prepare("SELECT id FROM purchase_main WHERE jci_number = ? FOR UPDATE");
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

    // Prepare statements once for better performance
    // Include supplier_name in lookup so different suppliers create separate rows for the same BOM row_id
    $stmt_find = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND row_id = ? AND job_card_number = ? AND supplier_name = ? LIMIT 1");
    
    // Prepare update statements
    // Detect if row_id column exists for safe updates
    $has_row_id = false;
    try {
        $stmt_col_rowid = $conn->query("SHOW COLUMNS FROM purchase_items LIKE 'row_id'");
        $has_row_id = $stmt_col_rowid->rowCount() > 0;
    } catch (Exception $e) { $has_row_id = false; }

    $stmt_update_wood = $conn->prepare(
        $has_row_id ?
        "UPDATE purchase_items SET 
            supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
            assigned_quantity = ?, price = ?, total = ?, date = ?, 
            invoice_number = ?, amount = ?, invoice_image = ?, 
            builty_number = ?, builty_image = ?, 
            length_ft = ?, width_ft = ?, thickness_inch = ?, row_id = ?, updated_at = NOW() 
         WHERE id = ?"
        :
        "UPDATE purchase_items SET 
            supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
            assigned_quantity = ?, price = ?, total = ?, date = ?, 
            invoice_number = ?, amount = ?, invoice_image = ?, 
            builty_number = ?, builty_image = ?, 
            length_ft = ?, width_ft = ?, thickness_inch = ?, updated_at = NOW() 
         WHERE id = ?"
    );
    
    $stmt_update_regular = $conn->prepare(
        $has_row_id ?
        "UPDATE purchase_items SET 
            supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
            assigned_quantity = ?, price = ?, total = ?, date = ?, 
            invoice_number = ?, amount = ?, invoice_image = ?, 
            builty_number = ?, builty_image = ?, row_id = ?, updated_at = NOW() 
         WHERE id = ?"
        :
        "UPDATE purchase_items SET 
            supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
            assigned_quantity = ?, price = ?, total = ?, date = ?, 
            invoice_number = ?, amount = ?, invoice_image = ?, 
            builty_number = ?, builty_image = ?, updated_at = NOW() 
         WHERE id = ?"
    );
    
    // Prepare insert statements
    $stmt_insert_wood = $conn->prepare("
        INSERT INTO purchase_items 
        (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
         assigned_quantity, price, total, date, invoice_number, amount, 
         invoice_image, builty_number, builty_image, 
         length_ft, width_ft, thickness_inch, row_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt_insert_regular = $conn->prepare("
        INSERT INTO purchase_items 
        (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
         assigned_quantity, price, total, date, invoice_number, amount, 
         invoice_image, builty_number, builty_image, row_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    // Process items in batch
    // Detect optional supplier_sequence column once
    $has_supplier_sequence = false;
    try {
        $stmt_col = $conn->query("SHOW COLUMNS FROM purchase_items LIKE 'supplier_sequence'");
        $has_supplier_sequence = $stmt_col->rowCount() > 0;
    } catch (Exception $e) {
        $has_supplier_sequence = false;
    }

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
        $existing_item_id = filter_var($item_data['existing_item_id'] ?? null, FILTER_VALIDATE_INT);

        if ($row_id === null || $rowIndex === null) {
            continue; // Skip if no row_id
        }

        // Validate required fields
        if (empty($supplier_name)) {
            continue; // Skip rows without supplier name
        }
        if ($assigned_quantity <= 0) {
            continue; // Skip rows with zero or negative quantity
        }
        if ($price < 0) {
            $price = 0; // Set negative price to zero
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
        $stmt_find->execute([$purchase_main_id, $row_id, $job_card_number, $supplier_name]);
        $existing_item = $stmt_find->fetch(PDO::FETCH_ASSOC);
        
        // Fallback: if not found but we have an explicit existing_item_id, use it (legacy rows with row_id=0)
        if (!$existing_item && $existing_item_id) {
            $stmt_by_id = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE id = ? LIMIT 1");
            $stmt_by_id->execute([$existing_item_id]);
            $existing_item = $stmt_by_id->fetch(PDO::FETCH_ASSOC);
        }

        // Validate against BOM quantity: do not allow total assigned across suppliers to exceed BOM
        try {
            // Fetch BOM quantity for this product using jci_number and product type/name
            $stmt_bom_qty = $conn->prepare("
                SELECT SUM(quantity) AS qty FROM (
                    SELECT bw.quantity AS quantity
                    FROM bom_wood bw 
                    JOIN bom_main bm ON bw.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND ? = 'Wood' AND bw.woodtype = ?
                    UNION ALL
                    SELECT bh.quantity AS quantity
                    FROM bom_hardware bh 
                    JOIN bom_main bm ON bh.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND ? = 'Hardware' AND bh.itemname = ?
                    UNION ALL
                    SELECT bg.quantity AS quantity
                    FROM bom_glow bg 
                    JOIN bom_main bm ON bg.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND ? = 'Glow' AND bg.glowtype = ?
                    UNION ALL
                    SELECT bp.quantity AS quantity
                    FROM bom_plynydf bp 
                    JOIN bom_main bm ON bp.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND ? = 'Plynydf'
                ) t
            ");
            $stmt_bom_qty->execute([
                $jci_number, $product_type, $product_name,
                $jci_number, $product_type, $product_name,
                $jci_number, $product_type, $product_name,
                $jci_number, $product_type
            ]);
            $bom_row = $stmt_bom_qty->fetch(PDO::FETCH_ASSOC);
            $bom_qty = floatval($bom_row['qty'] ?? 0);

            // Current total assigned for this product + job card (exclude current existing item if present)
            $params_sum = [$purchase_main_id, $job_card_number, $product_name];
            $sql_sum = "SELECT COALESCE(SUM(assigned_quantity),0) FROM purchase_items WHERE purchase_main_id = ? AND job_card_number = ? AND product_name = ?";
            if ($existing_item && isset($existing_item['id'])) {
                $sql_sum .= " AND id <> ?";
                $params_sum[] = $existing_item['id'];
            }
            $stmt_sum = $conn->prepare($sql_sum);
            $stmt_sum->execute($params_sum);
            $current_sum = floatval($stmt_sum->fetchColumn());
            $new_total = $current_sum + floatval($assigned_quantity);

            if ($bom_qty > 0 && $new_total > $bom_qty + 0.0001) {
                throw new Exception("Assigned quantity exceeds BOM quantity for {$product_name} in {$job_card_number}. Current: {$current_sum}, New: {$new_total}, BOM: {$bom_qty}");
            }
        } catch (Exception $vex) {
            // Stop processing this item on validation failure
            $conn->rollBack();
            echo json_encode(['success' => false, 'error' => $vex->getMessage()]);
            return;
        }

        if ($existing_item) {
            // Update existing item using prepared statements
            if ($is_superadmin || empty($existing_item['invoice_number'])) {
                if ($product_type === 'Wood') {
                    $params = [
                        $supplier_name, $product_type, $product_name, $job_card_number,
                        $assigned_quantity, $price, $total, $date,
                        $invoice_number, $total, $invoice_image_name,
                        $builty_number, $builty_image_name,
                        floatval($item_data['length_ft'] ?? 0),
                        floatval($item_data['width_ft'] ?? 0),
                        floatval($item_data['thickness_inch'] ?? 0)
                    ];
                    if ($has_row_id) { $params[] = $row_id; }
                    $params[] = $existing_item['id'];
                    $stmt_update_wood->execute($params);
                } else {
                    $params = [
                        $supplier_name, $product_type, $product_name, $job_card_number,
                        $assigned_quantity, $price, $total, $date,
                        $invoice_number, $total, $invoice_image_name,
                        $builty_number, $builty_image_name
                    ];
                    if ($has_row_id) { $params[] = $row_id; }
                    $params[] = $existing_item['id'];
                    $stmt_update_regular->execute($params);
                }
            }
            $updated_id = $existing_item['id'];
        } else {
            // Compute supplier_sequence if column exists: sequence per (purchase_main_id, job_card_number, product_name)
            $supplier_sequence = 1;
            if ($has_supplier_sequence) {
                $stmt_seq = $conn->prepare("SELECT COALESCE(MAX(supplier_sequence), 0) FROM purchase_items WHERE purchase_main_id = ? AND job_card_number = ? AND product_name = ?");
                $stmt_seq->execute([$purchase_main_id, $job_card_number, $product_name]);
                $supplier_sequence = intval($stmt_seq->fetchColumn()) + 1;
            }
            // Ensure row_id uniqueness for this (purchase_main_id, job_card_number, product_name)
            if ($has_row_id) {
                $stmt_chk = $conn->prepare("SELECT COUNT(*) FROM purchase_items WHERE purchase_main_id = ? AND job_card_number = ? AND product_name = ? AND row_id = ?");
                $stmt_chk->execute([$purchase_main_id, $job_card_number, $product_name, $row_id]);
                $exists_same_row_id = intval($stmt_chk->fetchColumn()) > 0;
                if ($exists_same_row_id) {
                    $stmt_cnt = $conn->prepare("SELECT COUNT(*) FROM purchase_items WHERE purchase_main_id = ? AND job_card_number = ? AND product_name = ?");
                    $stmt_cnt->execute([$purchase_main_id, $job_card_number, $product_name]);
                    $existing_count = intval($stmt_cnt->fetchColumn());
                    // Derive a unique row id based on base BOM row id
                    $row_id = ($row_id * 1000) + $existing_count + 1;
                }
            }
            // Insert new item using prepared statements
            if ($product_type === 'Wood') {
                $stmt_insert_wood->execute([
                    $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                    $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                    $invoice_image_name, $builty_number, $builty_image_name,
                    floatval($item_data['length_ft'] ?? 0),
                    floatval($item_data['width_ft'] ?? 0),
                    floatval($item_data['thickness_inch'] ?? 0),
                    $row_id
                ]);
            } else {
                $stmt_insert_regular->execute([
                    $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                    $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                    $invoice_image_name, $builty_number, $builty_image_name, $row_id
                ]);
            }
            $updated_id = $conn->lastInsertId();

            // If supplier_sequence exists, update the just-inserted row with computed sequence
            if ($has_supplier_sequence) {
                $stmt_seq_update = $conn->prepare("UPDATE purchase_items SET supplier_sequence = ? WHERE id = ?");
                $stmt_seq_update->execute([$supplier_sequence, $updated_id]);
            }
        }
    }

    // Don't automatically set purchase_created = 1, allow re-editing
    // $stmt_update_jci = $conn->prepare("UPDATE jci_main SET purchase_created = 1 WHERE jci_number = ?");
    // $stmt_update_jci->execute([$jci_number]);

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
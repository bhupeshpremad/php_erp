<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Enhanced multi-supplier save functionality
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
    echo json_encode(['success' => false, 'error' => 'Invalid data for multi-supplier save']);
    exit;
}

// Use global connection
global $conn;

// Define upload directories
$invoice_upload_dir = __DIR__ . '/uploads/invoice/';
$builty_upload_dir = __DIR__ . '/uploads/Builty/';

// Ensure upload directories exist
if (!is_dir($invoice_upload_dir)) { mkdir($invoice_upload_dir, 0777, true); }
if (!is_dir($builty_upload_dir)) { mkdir($builty_upload_dir, 0777, true); }

$response = ['success' => false, 'error' => 'An unknown error occurred.'];

try {
    $conn->beginTransaction();

    // Get or create purchase_main record
    $stmt_check = $conn->prepare("SELECT id FROM purchase_main WHERE jci_number = ? FOR UPDATE");
    $stmt_check->execute([$jci_number]);
    $existing_purchase = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_purchase) {
        $purchase_main_id = $existing_purchase['id'];
        $stmt_update = $conn->prepare("UPDATE purchase_main SET po_number = ?, sell_order_number = ?, bom_number = ?, updated_at = NOW() WHERE id = ?");
        $stmt_update->execute([$po_number, $sell_order_number, $bom_number, $purchase_main_id]);
    } else {
        $created_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        
        // Check if created_by column exists
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

    // Group items by product and job card to validate multi-supplier allocation
    $productGroups = [];
    foreach ($items as $item_data) {
        $product_key = $item_data['product_name'] . '_' . $item_data['job_card_number'];
        
        if (!isset($productGroups[$product_key])) {
            $productGroups[$product_key] = [
                'product_name' => $item_data['product_name'],
                'job_card_number' => $item_data['job_card_number'],
                'bom_quantity' => 0,
                'total_assigned' => 0,
                'suppliers' => []
            ];
        }
        
        $productGroups[$product_key]['suppliers'][] = $item_data;
        $productGroups[$product_key]['total_assigned'] += floatval($item_data['assigned_quantity']);
        
        // Get BOM quantity from first item (should be same for all suppliers of same product)
        if ($productGroups[$product_key]['bom_quantity'] == 0) {
            // Fetch BOM quantity from database for validation
            $stmt_bom = $conn->prepare("
                SELECT quantity FROM (
                    SELECT quantity, 'Wood' as type FROM bom_wood bw 
                    JOIN bom_main bm ON bw.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND bw.woodtype = ?
                    UNION ALL
                    SELECT quantity, 'Hardware' as type FROM bom_hardware bh 
                    JOIN bom_main bm ON bh.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND bh.itemname = ?
                    UNION ALL
                    SELECT quantity, 'Glow' as type FROM bom_glow bg 
                    JOIN bom_main bm ON bg.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND bg.glowtype = ?
                    UNION ALL
                    SELECT quantity, 'Plynydf' as type FROM bom_plynydf bp 
                    JOIN bom_main bm ON bp.bom_main_id = bm.id 
                    JOIN jci_main jm ON bm.id = jm.bom_id 
                    WHERE jm.jci_number = ? AND bp.quantity = ?
                ) as combined_bom LIMIT 1
            ");
            
            $product_name = $item_data['product_name'];
            $stmt_bom->execute([
                $jci_number, $product_name,
                $jci_number, $product_name,
                $jci_number, $product_name,
                $jci_number, $item_data['assigned_quantity'] // For Plynydf matching
            ]);
            
            $bom_result = $stmt_bom->fetch(PDO::FETCH_ASSOC);
            if ($bom_result) {
                $productGroups[$product_key]['bom_quantity'] = floatval($bom_result['quantity']);
            }
        }
    }

    // Validate allocation doesn't exceed BOM quantities
    $validation_errors = [];
    foreach ($productGroups as $key => $group) {
        if ($group['total_assigned'] > $group['bom_quantity'] + 0.001) { // Allow small floating point tolerance
            $validation_errors[] = "Product '{$group['product_name']}' in job card '{$group['job_card_number']}': Total assigned quantity ({$group['total_assigned']}) exceeds BOM quantity ({$group['bom_quantity']})";
        }
    }

    if (!empty($validation_errors)) {
        throw new Exception("Allocation validation failed:\n" . implode("\n", $validation_errors));
    }

    // Prepare statements for batch processing
    $stmt_find = $conn->prepare("SELECT id, invoice_number, builty_number, invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ? AND row_id = ? AND job_card_number = ? AND supplier_name = ? LIMIT 1");
    
    $stmt_update = $conn->prepare("
        UPDATE purchase_items SET 
        supplier_name = ?, product_type = ?, product_name = ?, job_card_number = ?, 
        assigned_quantity = ?, price = ?, total = ?, date = ?, 
        invoice_number = ?, amount = ?, invoice_image = ?, 
        builty_number = ?, builty_image = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    
    $stmt_insert = $conn->prepare("
        INSERT INTO purchase_items 
        (purchase_main_id, supplier_name, product_type, product_name, job_card_number, 
         assigned_quantity, price, total, date, invoice_number, amount, 
         invoice_image, builty_number, builty_image, row_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $processed_items = 0;
    $supplier_summary = [];

    // Process each item
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

        // Skip empty rows
        if (empty($supplier_name) || $assigned_quantity <= 0) {
            continue;
        }

        // Handle file uploads with enhanced security
        $existing_invoice_image = $item_data['existing_invoice_image'] ?? null;
        $existing_builty_image = $item_data['existing_builty_image'] ?? null;
        
        $invoice_image_name = $existing_invoice_image;
        $builty_image_name = $existing_builty_image;

        // File upload handling with MIME type validation
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        // Handle invoice image upload with MIME validation
        $file_key_invoice = 'invoice_image_row_' . $row_id;
        if (isset($_FILES[$file_key_invoice]) && $_FILES[$file_key_invoice]['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES[$file_key_invoice]['tmp_name'];
            $file_size = $_FILES[$file_key_invoice]['size'];
            $file_ext = strtolower(pathinfo($_FILES[$file_key_invoice]['name'], PATHINFO_EXTENSION));
            $file_mime = mime_content_type($file_tmp_path);

            if ($file_size <= $max_file_size && in_array($file_ext, $allowed_extensions) && in_array($file_mime, $allowed_mime_types)) {
                $invoice_image_name = bin2hex(random_bytes(16)) . '_inv.' . $file_ext;
                move_uploaded_file($file_tmp_path, $invoice_upload_dir . $invoice_image_name);
            }
        }

        // Handle builty image upload with MIME validation
        $file_key_builty = 'builty_image_row_' . $row_id;
        if (isset($_FILES[$file_key_builty]) && $_FILES[$file_key_builty]['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES[$file_key_builty]['tmp_name'];
            $file_size = $_FILES[$file_key_builty]['size'];
            $file_ext = strtolower(pathinfo($_FILES[$file_key_builty]['name'], PATHINFO_EXTENSION));
            $file_mime = mime_content_type($file_tmp_path);

            if ($file_size <= $max_file_size && in_array($file_ext, $allowed_extensions) && in_array($file_mime, $allowed_mime_types)) {
                $builty_image_name = bin2hex(random_bytes(16)) . '_blt.' . $file_ext;
                move_uploaded_file($file_tmp_path, $builty_upload_dir . $builty_image_name);
            }
        }

        // For multi-supplier: Generate unique row_id for each supplier of same product
        $unique_row_id = $row_id;
        if ($row_id !== null) {
            // Check if this is additional supplier for same product
            $stmt_count = $conn->prepare("SELECT COUNT(*) FROM purchase_items WHERE purchase_main_id = ? AND product_name = ? AND job_card_number = ?");
            $stmt_count->execute([$purchase_main_id, $product_name, $job_card_number]);
            $existing_count = $stmt_count->fetchColumn();
            
            if ($existing_count > 0) {
                // Generate unique row_id for additional suppliers
                $unique_row_id = $row_id * 1000 + $existing_count + 1;
            }
        }

        // Check if exact item exists (same supplier for same product)
        $stmt_find->execute([$purchase_main_id, $unique_row_id, $job_card_number, $supplier_name]);
        $existing_item = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Update existing item
            if ($is_superadmin || empty($existing_item['invoice_number'])) {
                $stmt_update->execute([
                    $supplier_name, $product_type, $product_name, $job_card_number,
                    $assigned_quantity, $price, $total, $date,
                    $invoice_number, $total, $invoice_image_name,
                    $builty_number, $builty_image_name,
                    $existing_item['id']
                ]);
            }
        } else {
            // Insert new item with unique row_id
            $stmt_insert->execute([
                $purchase_main_id, $supplier_name, $product_type, $product_name, $job_card_number,
                $assigned_quantity, $price, $total, $date, $invoice_number, $total,
                $invoice_image_name, $builty_number, $builty_image_name, $unique_row_id
            ]);
        }

        $processed_items++;
        
        // Build supplier summary
        if (!isset($supplier_summary[$supplier_name])) {
            $supplier_summary[$supplier_name] = [
                'total_quantity' => 0,
                'total_amount' => 0,
                'products' => []
            ];
        }
        
        $supplier_summary[$supplier_name]['total_quantity'] += $assigned_quantity;
        $supplier_summary[$supplier_name]['total_amount'] += $total;
        $supplier_summary[$supplier_name]['products'][] = $product_name;
    }

    // Update JCI main table
    $stmt_update_jci = $conn->prepare("UPDATE jci_main SET purchase_created = 1 WHERE jci_number = ?");
    $stmt_update_jci->execute([$jci_number]);

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => "Multi-supplier purchase saved successfully. Processed {$processed_items} items across " . count($supplier_summary) . " suppliers.",
        'processed_items' => $processed_items,
        'supplier_summary' => $supplier_summary,
        'product_groups' => array_map(function($group) {
            return [
                'product_name' => $group['product_name'],
                'job_card' => $group['job_card_number'],
                'bom_quantity' => $group['bom_quantity'],
                'total_assigned' => $group['total_assigned'],
                'remaining' => $group['bom_quantity'] - $group['total_assigned'],
                'supplier_count' => count($group['suppliers'])
            ];
        }, $productGroups)
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Application error: ' . $e->getMessage()]);
}
?>
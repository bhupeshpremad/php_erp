<?php
// Basic error handling
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Increase limits for large data
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
ini_set('max_input_vars', 5000);

include_once __DIR__ . '/../../config/config.php';
require_once ROOT_DIR_PATH . 'core/utils.php';

global $conn;

global $conn;

// Define the correct, absolute path for uploads
$uploadDir = rtrim(ROOT_DIR_PATH, '/\\') . '/assets/images/upload/quotation/';
$excelUploadDir = rtrim(ROOT_DIR_PATH, '/\\') . '/modules/quotation/uploads/';

// Ensure the upload directories exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
        exit;
    }
}
if (!file_exists($excelUploadDir)) {
    if (!mkdir($excelUploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create Excel upload directory.']);
        exit;
    }
}

// Basic validation for main form fields
$requiredFields = ['lead_id', 'quotation_date', 'quotation_number', 'customer_name', 'customer_email', 'customer_phone'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required."]);
        exit;
    }
}

function insertProducts($conn, $quotationId, $productsJson, $uploadDir) {
    $products = json_decode($productsJson, true);
    if (!is_array($products) || empty($products)) {
        return;
    }

    try {
        $productStmt = $conn->prepare(
            "INSERT INTO quotation_products (quotation_id, item_name, item_code, assembly, item_w, item_d, item_h, box_w, box_d, box_h, cbm, wood_type, no_of_packet, quantity, price_usd, total_price_usd, comments, product_image_name) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($products as $index => $product) {
            if (empty($product['item_name'])) {
                continue;
            }

            $imageName = $product['existing_image_name'] ?? null;

            // Handle file upload for individual product images
            $fileInputName = 'product_images_' . $index;
            if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
                $fileTmpName = $_FILES[$fileInputName]['tmp_name'];
                $fileName = Utils::sanitizeFilename(basename($_FILES[$fileInputName]['name']));
                $fileSize = $_FILES[$fileInputName]['size'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $maxFileSize = 2 * 1024 * 1024; // 2 MB

                if (in_array($fileExt, $allowedExtensions) && $fileSize <= $maxFileSize) {
                    $newFileName = 'prod_' . $quotationId . '_' . ($index + 1) . '_' . time() . '.' . $fileExt;
                    $targetFilePath = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                        $imageName = $newFileName;
                    }
                }
            }

            $quantity = floatval($product['quantity'] ?? 0);
            $priceUsd = floatval($product['price_usd'] ?? 0);
            $totalPrice = $quantity * $priceUsd;

            $productStmt->execute([
                $quotationId,
                $product['item_name'],
                $product['item_code'] ?? '',
                $product['assembly'] ?? '',
                !empty($product['item_w']) ? floatval($product['item_w']) : null,
                !empty($product['item_d']) ? floatval($product['item_d']) : null,
                !empty($product['item_h']) ? floatval($product['item_h']) : null,
                !empty($product['box_w']) ? floatval($product['box_w']) : null,
                !empty($product['box_d']) ? floatval($product['box_d']) : null,
                !empty($product['box_h']) ? floatval($product['box_h']) : null,
                !empty($product['cbm']) ? floatval($product['cbm']) : null,
                $product['wood_type'] ?? '',
                !empty($product['no_of_packet']) ? intval($product['no_of_packet']) : null,
                $quantity,
                $priceUsd,
                $totalPrice,
                $product['comments'] ?? '',
                $imageName
            ]);
        }
        
    } catch (Exception $e) {
        throw $e;
    }
}

// Handle Excel file upload
$excelFileName = null;
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpName = $_FILES['excel_file']['tmp_name'];
    $fileName = Utils::sanitizeFilename(basename($_FILES['excel_file']['name']));
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedExtensions = ['xlsx', 'xls', 'csv'];
    if (in_array($fileExt, $allowedExtensions)) {
        $newFileName = 'quotation_' . time() . '.' . $fileExt;
        $targetFilePath = $excelUploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmpName, $targetFilePath)) {
            $excelFileName = $newFileName;
        }
    }
}

try {
    $conn->beginTransaction();
    
    if (isset($_POST['quotation_id']) && !empty($_POST['quotation_id'])) {
        // UPDATE EXISTING QUOTATION
        $quotationId = intval($_POST['quotation_id']);

        // Check if the quotation is locked
        require_once ROOT_DIR_PATH . 'core/LockSystem.php';
        $lockSystem = new LockSystem($conn);
        $lockInfo = $lockSystem->isLocked('quotations', $quotationId);
        $isLocked = is_array($lockInfo) && (int)($lockInfo['is_locked'] ?? 0) === 1;
        if ($isLocked && (($_SESSION['user_type'] ?? '') !== 'superadmin')) {
            echo json_encode(['success' => false, 'message' => 'This Quotation is locked and cannot be updated.']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE quotations SET lead_id = ?, quotation_date = ?, quotation_number = ?, customer_name = ?, customer_email = ?, customer_phone = ?, delivery_term = ?, terms_of_delivery = ?, excel_file = COALESCE(?, excel_file) WHERE id = ?");
        $stmt->execute([
            $_POST['lead_id'], $_POST['quotation_date'], $_POST['quotation_number'],
            $_POST['customer_name'], $_POST['customer_email'], $_POST['customer_phone'],
            $_POST['delivery_term'] ?? '', $_POST['terms_of_delivery'] ?? '',
            $excelFileName,
            $quotationId
        ]);

        // Delete old products
        $deleteStmt = $conn->prepare("DELETE FROM quotation_products WHERE quotation_id = ?");
        $deleteStmt->execute([$quotationId]);

    } else {
        // CREATE NEW QUOTATION
        $stmt = $conn->prepare("INSERT INTO quotations (lead_id, quotation_date, quotation_number, customer_name, customer_email, customer_phone, delivery_term, terms_of_delivery, excel_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['lead_id'], $_POST['quotation_date'], $_POST['quotation_number'],
            $_POST['customer_name'], $_POST['customer_email'], $_POST['customer_phone'],
            $_POST['delivery_term'] ?? '', $_POST['terms_of_delivery'] ?? '',
            $excelFileName
        ]);
        $quotationId = $conn->lastInsertId();
    }
    
    // Insert products
    if (!empty($_POST['products'])) {
        insertProducts($conn, $quotationId, $_POST['products'], $uploadDir);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Quotation saved successfully.', 'quotation_id' => $quotationId]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
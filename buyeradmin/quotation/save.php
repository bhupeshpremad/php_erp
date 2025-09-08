<?php
session_start();
include '../../config/config.php';
require_once ROOT_DIR_PATH . 'core/utils.php'; // For sanitizeFilename

header('Content-Type: application/json');

if (!isset($_SESSION['buyer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$buyer_id = $_SESSION['buyer_id'];

$quotation_id = $_POST['quotation_id'] ?? null;
$quotation_number = $_POST['quotation_number'] ?? '';
$quotation_date = $_POST['quotation_date'] ?? '';
$customer_name = $_POST['customer_name'] ?? '';
$customer_email = $_POST['customer_email'] ?? '';
$customer_phone = $_POST['customer_phone'] ?? '';
$delivery_term = $_POST['delivery_term'] ?? '';
$terms_of_delivery = $_POST['terms_of_delivery'] ?? '';
$total_amount = 0;

$products = [];
// Iterate through product data submitted with dynamic names like item_name_0, quantity_1, etc.
$i = 0;
while (isset($_POST['item_name_' . $i])) {
    $product = [
        'item_name' => $_POST['item_name_' . $i],
        'item_code' => $_POST['item_code_' . $i] ?? '',
        'assembly' => $_POST['assembly_' . $i] ?? '',
        'item_w' => $_POST['item_w_' . $i] ?? 0,
        'item_d' => $_POST['item_d_' . $i] ?? 0,
        'item_h' => $_POST['item_h_' . $i] ?? 0,
        'box_w' => $_POST['box_w_' . $i] ?? 0,
        'box_d' => $_POST['box_d_' . $i] ?? 0,
        'box_h' => $_POST['box_h_' . $i] ?? 0,
        'cbm' => $_POST['cbm_' . $i] ?? 0,
        'wood_type' => $_POST['wood_type_' . $i] ?? '',
        'no_of_packet' => $_POST['no_of_packet_' . $i] ?? 0,
        'quantity' => $_POST['quantity_' . $i] ?? 0,
        'price_usd' => $_POST['price_usd_' . $i] ?? 0,
        'total_usd' => $_POST['total_usd_' . $i] ?? 0,
        'comments' => $_POST['comments_' . $i] ?? '',
        'image_path' => null
    ];

    $total_amount += $product['total_usd'];
    
    // Handle product image upload
    $imageFileName = null;
    $fileInputName = 'product_images_' . $i;

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = ROOT_DIR_PATH . 'assets/images/upload/buyer_quotations/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = Utils::sanitizeFilename(basename($_FILES[$fileInputName]['name']));
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = 'buyer_quote_product_' . $buyer_id . '_' . time() . '_' . $i . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $imageFileName = $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image for product ' . ($i + 1) . '.']);
            exit;
        }
    }
    $product['image_path'] = $imageFileName;
    $products[] = $product;
    $i++;
}

// Basic validation
if (empty($quotation_number) || empty($quotation_date) || empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($delivery_term) || empty($terms_of_delivery)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required quotation details.']);
    exit;
}

if (empty($products)) {
    echo json_encode(['success' => false, 'message' => 'Please add at least one product.']);
    exit;
}

try {
    $conn->beginTransaction();

    // Insert or update main quotation
    if ($quotation_id) {
        // Update existing quotation
        $stmt = $conn->prepare("UPDATE buyer_quotations SET 
            quotation_number = ?, quotation_date = ?, customer_name = ?, customer_email = ?, customer_phone = ?, 
            delivery_term = ?, terms_of_delivery = ?, total_amount = ?, updated_at = NOW() WHERE id = ? AND buyer_id = ?");
        $stmt->execute([
            $quotation_number, $quotation_date, $customer_name, $customer_email, $customer_phone, 
            $delivery_term, $terms_of_delivery, $total_amount, $quotation_id, $buyer_id
        ]);
        $main_quotation_id = $quotation_id;
        
        // Delete old quotation products for update
        $stmt = $conn->prepare("DELETE FROM quotation_products WHERE quotation_id = ?");
        $stmt->execute([$main_quotation_id]);

    } else {
        // Insert new quotation
        $stmt = $conn->prepare("INSERT INTO buyer_quotations (
            buyer_id, rfq_reference, quotation_number, quotation_date, customer_name, customer_email, customer_phone,
            delivery_term, terms_of_delivery, total_amount, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $buyer_id, 'N/A', $quotation_number, $quotation_date, $customer_name, $customer_email, $customer_phone,
            $delivery_term, $terms_of_delivery, $total_amount
        ]);
        $main_quotation_id = $conn->lastInsertId();
    }

    // Insert products into quotation_products table
    foreach ($products as $product) {
        $stmt = $conn->prepare("INSERT INTO quotation_products (
            quotation_id, item_name, item_code, assembly, item_w, item_d, item_h, box_w, box_d, box_h, cbm,
            wood_type, no_of_packet, quantity, price_usd, total_usd, comments, product_image_name, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $main_quotation_id, $product['item_name'], $product['item_code'], $product['assembly'],
            $product['item_w'], $product['item_d'], $product['item_h'], $product['box_w'], $product['box_d'],
            $product['box_h'], $product['cbm'], $product['wood_type'], $product['no_of_packet'],
            $product['quantity'], $product['price_usd'], $product['total_usd'], $product['comments'], $product['image_path']
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Quotation saved successfully!']);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

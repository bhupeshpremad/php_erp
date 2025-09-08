<?php
session_start();
include '../../config/config.php';
require_once ROOT_DIR_PATH . 'core/utils.php'; // For sanitizeFilename

header('Content-Type: application/json');

if (!isset($_SESSION['buyer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$quotationId = $_POST['id'] ?? null;

if (!$quotationId) {
    echo json_encode(['success' => false, 'message' => 'Quotation ID is required.']);
    exit;
}

$buyer_id = $_SESSION['buyer_id'];

try {
    $conn->beginTransaction();

    // Verify ownership of the quotation
    $stmt = $conn->prepare("SELECT id FROM buyer_quotations WHERE id = ? AND buyer_id = ?");
    $stmt->execute([$quotationId, $buyer_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Quotation not found or you do not have permission to delete it.");
    }

    // Fetch product images associated with the quotation
    $stmt = $conn->prepare("SELECT product_image_name FROM quotation_products WHERE quotation_id = ?");
    $stmt->execute([$quotationId]);
    $productImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $uploadDir = ROOT_DIR_PATH . 'assets/images/upload/buyer_quotations/';

    // Delete physical image files
    foreach ($productImages as $imageName) {
        if (!empty($imageName) && file_exists($uploadDir . $imageName)) {
            unlink($uploadDir . $imageName);
        }
    }

    // Delete products associated with the quotation
    $stmt = $conn->prepare("DELETE FROM quotation_products WHERE quotation_id = ?");
    $stmt->execute([$quotationId]);

    // Delete the main quotation
    $stmt = $conn->prepare("DELETE FROM buyer_quotations WHERE id = ?");
    $stmt->execute([$quotationId]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Quotation and associated products deleted successfully.']);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

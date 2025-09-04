<?php

include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($conn) || !$conn instanceof PDO) {
    echo json_encode(['success' => false, 'message' => 'Database connection not initialized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_id = (int)($_POST['po_id'] ?? 0);

    if ($po_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid PO ID provided.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT product_code, product_name, quantity, unit, price, total_amount, product_image FROM po_items WHERE po_id = :po_id ORDER BY id ASC");
        $stmt->bindValue(':po_id', $po_id, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($items === false) {
             throw new Exception('PDO fetch operation failed.');
        }

        echo json_encode(['success' => true, 'items' => $items]);
        exit;

    } catch (PDOException $e) {
        error_log("Database error fetching PO items: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'A database error occurred while fetching items. Please try again.']);
        exit;
    } catch (Exception $e) {
        error_log("Error fetching PO items: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit;
<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: text/plain');

try {
    // Get all po_id from jci_main
    $stmt = $conn->query("SELECT DISTINCT po_id FROM jci_main");
    $po_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$po_ids) {
        echo "No po_id found in jci_main table.\n";
        exit;
    }

    // Prepare statement to check po_id in po_main
    $placeholders = implode(',', array_fill(0, count($po_ids), '?'));
    $sql = "SELECT id, po_number FROM po_main WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($po_ids);
    $po_main_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $po_main_map = [];
    foreach ($po_main_rows as $row) {
        $po_main_map[$row['id']] = $row['po_number'];
    }

    // Check for po_id in jci_main that do not exist in po_main
    $missing_po_ids = [];
    foreach ($po_ids as $po_id) {
        if (!isset($po_main_map[$po_id])) {
            $missing_po_ids[] = $po_id;
        }
    }

    if ($missing_po_ids) {
        echo "The following po_id(s) exist in jci_main but not found in po_main:\n";
        foreach ($missing_po_ids as $missing_id) {
            echo "- po_id: $missing_id\n";
        }
    } else {
        echo "All po_id in jci_main have matching entries in po_main.\n";
    }

    // List po_id with empty or null po_number in po_main
    $empty_po_number_ids = [];
    foreach ($po_main_map as $id => $po_number) {
        if (empty($po_number)) {
            $empty_po_number_ids[] = $id;
        }
    }

    if ($empty_po_number_ids) {
        echo "\nThe following po_id(s) have empty or null po_number in po_main:\n";
        foreach ($empty_po_number_ids as $id) {
            echo "- po_id: $id\n";
        }
    } else {
        echo "\nAll po_id in po_main have non-empty po_number.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>

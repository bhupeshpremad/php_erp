<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$section = $_GET['section'] ?? '';

if (empty($section) || !in_array($section, ['wood', 'glow', 'plynydf', 'hardware', 'labour', 'factory', 'margin'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid section provided.']);
    exit;
}

$bom_id = $data['id'] ?? null;
$bom_number = $data['bom_number'] ?? '';
$costing_sheet_number = $data['costing_sheet_number'] ?? '';
$client_name = $data['client_name'] ?? '';
$created_date = $data['created_date'] ?? '';
$prepared_by = $data['prepared_by'] ?? '';

try {
    $conn->beginTransaction();

    // If bom_id is not present, check if a BOM with this number already exists
    if (empty($bom_id)) {
        $stmtCheck = $conn->prepare("SELECT id FROM bom_main WHERE bom_number = ?");
        $stmtCheck->execute([$bom_number]);
        $existing_bom = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing_bom) {
            $bom_id = $existing_bom['id'];
        }
    }

    if (empty($bom_id)) {
        if (empty($bom_number) || empty($costing_sheet_number) || empty($client_name) || empty($created_date) || empty($prepared_by)) {
            echo json_encode(['success' => false, 'message' => 'Please fill all required main fields before saving items.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO bom_main (bom_number, costing_sheet_number, client_name, prepared_by, order_date, delivery_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$bom_number, $costing_sheet_number, $client_name, $prepared_by, $created_date, $created_date]);
        $bom_id = $conn->lastInsertId();
    } else {
        // Update main details only if they are sent (e.g., not an item-only save)
        if (!empty($costing_sheet_number) && !empty($client_name)) {
            $stmt = $conn->prepare("UPDATE bom_main SET costing_sheet_number = ?, client_name = ?, prepared_by = ?, order_date = ?, delivery_date = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$costing_sheet_number, $client_name, $prepared_by, $created_date, $created_date, $bom_id]);
        }
    }

    $items = $data[$section] ?? [];

    if (!empty($items)) {
        $tableName = 'bom_' . $section;
        
        // Clear existing items for this section and bom_id
        $deleteStmt = $conn->prepare("DELETE FROM $tableName WHERE bom_main_id = ?");
        $deleteStmt->execute([$bom_id]);

        switch ($section) {
            case 'wood':
                $stmt = $conn->prepare("INSERT INTO bom_wood (bom_main_id, woodtype, length_ft, width_ft, thickness_inch, quantity, price, cft, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $width_ft = ($item['width'] ?? 0) / 12;
                    $stmt->execute([$bom_id, $item['woodtype'], $item['length'], $width_ft, $item['thickness'], $item['quantity'], $item['price'], $item['cft'], $item['total']]);
                }
                break;
            case 'glow':
                $stmt = $conn->prepare("INSERT INTO bom_glow (bom_main_id, glowtype, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$bom_id, $item['glowtype'], $item['quantity'], $item['price'], $item['total']]);
                }
                break;
            case 'plynydf':
                $stmt = $conn->prepare("INSERT INTO bom_plynydf (bom_main_id, quantity, width, length, price, total) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$bom_id, $item['quantity'], $item['width'], $item['length'], $item['price'], $item['total']]);
                }
                break;
            case 'hardware':
                $stmt = $conn->prepare("INSERT INTO bom_hardware (bom_main_id, itemname, quantity, price, totalprice) VALUES (?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$bom_id, $item['itemname'], $item['quantity'], $item['price'], $item['totalprice']]);
                }
                break;
            case 'labour':
                $stmt = $conn->prepare("INSERT INTO bom_labour (bom_main_id, itemname, quantity, price, totalprice) VALUES (?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$bom_id, $item['itemname'], $item['quantity'], $item['price'], $item['totalprice']]);
                }
                break;
            case 'factory':
                $stmt = $conn->prepare("INSERT INTO bom_factory (bom_main_id, total_amount, factory_cost, updated_total) VALUES (?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$bom_id, $item['total_amount'], $item['factory_cost'], $item['updated_total']]);
                }
                break;
            case 'margin':
                $stmt = $conn->prepare("INSERT INTO bom_margin (bom_main_id, total_amount, margin_cost, updated_total) VALUES (?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$bom_id, $item['total_amount'], $item['margin_cost'], $item['updated_total']]);
                }
                break;
        }
    }

    // After saving items, update grand_total_amount in bom_main
    $stmtTotal = $conn->prepare("
        SELECT 
            COALESCE(SUM(total), 0) AS wood_total
        FROM bom_wood WHERE bom_main_id = ?
    ");
    $stmtTotal->execute([$bom_id]);
    $wood_total = (float)$stmtTotal->fetchColumn();

    $stmtTotal = $conn->prepare("
        SELECT 
            COALESCE(SUM(total), 0) AS glow_total
        FROM bom_glow WHERE bom_main_id = ?
    ");
    $stmtTotal->execute([$bom_id]);
    $glow_total = (float)$stmtTotal->fetchColumn();

    $stmtTotal = $conn->prepare("
        SELECT 
            COALESCE(SUM(total), 0) AS plynydf_total
        FROM bom_plynydf WHERE bom_main_id = ?
    ");
    $stmtTotal->execute([$bom_id]);
    $plynydf_total = (float)$stmtTotal->fetchColumn();

    $stmtTotal = $conn->prepare("
        SELECT 
            COALESCE(SUM(totalprice), 0) AS hardware_total
        FROM bom_hardware WHERE bom_main_id = ?
    ");
    $stmtTotal->execute([$bom_id]);
    $hardware_total = (float)$stmtTotal->fetchColumn();

    $stmtTotal = $conn->prepare("
        SELECT 
            COALESCE(SUM(totalprice), 0) AS labour_total
        FROM bom_labour WHERE bom_main_id = ?
    ");
    $stmtTotal->execute([$bom_id]);
    $labour_total = (float)$stmtTotal->fetchColumn();

    // Get factory cost
    $stmtFactory = $conn->prepare("SELECT COALESCE(factory_cost, 0) FROM bom_factory WHERE bom_main_id = ? LIMIT 1");
    $stmtFactory->execute([$bom_id]);
    $saved_factory_cost = (float)$stmtFactory->fetchColumn();

    // Get margin cost
    $stmtMargin = $conn->prepare("SELECT COALESCE(margin_cost, 0) FROM bom_margin WHERE bom_main_id = ? LIMIT 1");
    $stmtMargin->execute([$bom_id]);
    $saved_margin_cost = (float)$stmtMargin->fetchColumn();

    // Calculate main total (wood + glow + plynydf + hardware + labour)
    $main_total = $wood_total + $glow_total + $plynydf_total + $hardware_total + $labour_total;

    // Calculate factory cost as 15% of main total
    $factory_cost = round($main_total * 0.15, 2);

    // Calculate margin as 15% of (main total + factory cost)
    $margin_cost = round(($main_total + $factory_cost) * 0.15, 2);

    // Auto-save factory cost in bom_factory table if not manually saved
    if ($section !== 'factory') {
        $deleteFactory = $conn->prepare("DELETE FROM bom_factory WHERE bom_main_id = ?");
        $deleteFactory->execute([$bom_id]);
        $insertFactory = $conn->prepare("INSERT INTO bom_factory (bom_main_id, total_amount, factory_cost, updated_total) VALUES (?, ?, ?, ?)");
        $insertFactory->execute([$bom_id, $main_total, $factory_cost, $main_total + $factory_cost]);
    }

    // Auto-save margin cost in bom_margin table if not manually saved
    if ($section !== 'margin') {
        $deleteMargin = $conn->prepare("DELETE FROM bom_margin WHERE bom_main_id = ?");
        $deleteMargin->execute([$bom_id]);
        $insertMargin = $conn->prepare("INSERT INTO bom_margin (bom_main_id, total_amount, margin_cost, updated_total) VALUES (?, ?, ?, ?)");
        $insertMargin->execute([$bom_id, $main_total + $factory_cost, $margin_cost, $main_total + $factory_cost + $margin_cost]);
    }

    // Use saved costs if available, otherwise use calculated
    $final_factory_cost = $saved_factory_cost > 0 ? $saved_factory_cost : $factory_cost;
    $final_margin_cost = $saved_margin_cost > 0 ? $saved_margin_cost : $margin_cost;
    
    // Calculate grand total
    $grand_total = $main_total + $final_factory_cost + $final_margin_cost;

    $stmtUpdate = $conn->prepare("UPDATE bom_main SET grand_total_amount = ? WHERE id = ?");
    $stmtUpdate->execute([$grand_total, $bom_id]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => ucfirst($section) . ' data saved successfully.', 'bom_id' => $bom_id]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error saving BOM section $section: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error saving BOM: ' . $e->getMessage()]);
}

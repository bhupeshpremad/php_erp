<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
session_start();

header('Content-Type: application/json');

global $conn;

try {
    $conn->beginTransaction();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $sell_order_id = filter_input(INPUT_POST, 'sell_order_id', FILTER_VALIDATE_INT);
    $po_number = $_POST['po_number'] ?? '';
    $bom_id = filter_input(INPUT_POST, 'bom_id', FILTER_VALIDATE_INT);
    $sell_order_number = $_POST['sell_order_number'] ?? '';
    $base_jci_number = $_POST['base_jci_number'] ?? '';
    $created_by = $_POST['created_by'] ?? '';
    $jci_date = $_POST['jci_date'] ?? '';

    if (!$sell_order_id || empty($base_jci_number) || empty($created_by) || empty($jci_date)) {
        throw new Exception("Missing required main JCI details (Sell Order ID, Base JCI Number, Created By, Job Card Date).");
    }
    
    // Get PO ID from sell order
    $stmt_po = $conn->prepare("SELECT po_id FROM sell_order WHERE id = ?");
    $stmt_po->execute([$sell_order_id]);
    $po_result = $stmt_po->fetch(PDO::FETCH_ASSOC);
    if (!$po_result) {
        throw new Exception("PO not found for selected Sell Order.");
    }
    $po_id = $po_result['po_id'];

    $jci_main_id = $id;

    if ($jci_main_id) {
        $stmt = $conn->prepare("UPDATE jci_main SET
            po_id = ?,
            sell_order_number = ?,
            created_by = ?,
            jci_date = ?,
            bom_id = ?
            WHERE id = ?"
        );
        $stmt->execute([$po_id, $sell_order_number, $created_by, $jci_date, $bom_id, $jci_main_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO jci_main (
            po_id,
            bom_id,
            sell_order_number,
            jci_number,
            created_by,
            jci_date
        ) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$po_id, $bom_id, $sell_order_number, $base_jci_number, $created_by, $jci_date]);
        $jci_main_id = $conn->lastInsertId();

        if (!$jci_main_id) {
            throw new Exception("Failed to insert main JCI record.");
        }
    }

    $submitted_item_ids = [];
    $jci_item_ids = $_POST['jci_item_id'] ?? [];
    $po_product_ids = $_POST['po_product_id'] ?? [];
    $product_names = $_POST['product_name'] ?? [];
    $item_codes = $_POST['item_code'] ?? [];
    $original_po_quantities = $_POST['original_po_quantity'] ?? [];
    $assigned_quantities = $_POST['assign_quantity'] ?? [];
    $labour_costs = $_POST['labour_cost'] ?? [];
    $totals = $_POST['total'] ?? [];
    $delivery_dates = $_POST['delivery_date'] ?? [];
    $item_job_card_dates = $_POST['job_card_date'] ?? [];
    $item_job_card_types = $_POST['job_card_type'] ?? [];
    $contracture_names = $_POST['contracture_name'] ?? [];

    $num_items = count($po_product_ids);

    for ($i = 0; $i < $num_items; $i++) {
        $item_id = filter_var($jci_item_ids[$i], FILTER_VALIDATE_INT);
        $po_prod_id = filter_var($po_product_ids[$i], FILTER_VALIDATE_INT);
        $prod_name = (string)($product_names[$i] ?? '');
        $it_code = (string)($item_codes[$i] ?? '');
        $original_qty = filter_var($original_po_quantities[$i], FILTER_VALIDATE_FLOAT);
        $assign_qty = filter_var($assigned_quantities[$i], FILTER_VALIDATE_FLOAT);
        $lab_cost = filter_var($labour_costs[$i], FILTER_VALIDATE_FLOAT);
        $item_total = filter_var($totals[$i], FILTER_VALIDATE_FLOAT);
        $del_date = (string)($delivery_dates[$i] ?? '');
        $item_jci_date = (string)($item_job_card_dates[$i] ?? '');
        $item_jci_type = (string)($item_job_card_types[$i] ?? '');
        $contract_name = (string)($contracture_names[$i] ?? '');

        if (!$po_prod_id || empty($prod_name) || $assign_qty === false || $lab_cost === false || empty($del_date) || empty($item_jci_date)) {
            throw new Exception("Missing or invalid details for item " . ($i + 1) . ".");
        }

        if ($item_jci_type === 'Contracture' && empty($contract_name)) {
            throw new Exception("Contracture Name is required for 'Contracture' type on item " . ($i + 1) . ".");
        }
        if ($item_jci_type !== 'Contracture') {
            $contract_name = NULL;
        }

        $job_card_number_prefix = "JOB-" . date('Y', strtotime($item_jci_date)) . "-" . substr($base_jci_number, -4);
        $current_job_card_number = $job_card_number_prefix . "-" . ($i + 1);

        $stmt_po_item = $conn->prepare("SELECT product_name, quantity FROM po_items WHERE id = ?");
        $stmt_po_item->execute([$po_prod_id]);
        $po_item_data = $stmt_po_item->fetch(PDO::FETCH_ASSOC);

        if (!$po_item_data) {
            throw new Exception("Product details not found for PO Product ID: " . $po_prod_id . " on item " . ($i + 1) . ".");
        }

        $auth_product_name = $po_item_data['product_name'];
        $auth_item_code = $it_code; // Use submitted item code
        $auth_original_po_quantity = $po_item_data['quantity'];

        if ($assign_qty > $auth_original_po_quantity) {
             throw new Exception("Assigned quantity for '" . $auth_product_name . "' (item " . ($i + 1) . ") exceeds original PO quantity (" . $auth_original_po_quantity . ").");
        }

        $stmt_sum_assigned = $conn->prepare("SELECT SUM(quantity) AS total_assigned FROM jci_items WHERE po_product_id = ? AND jci_id != ?");
        $stmt_sum_assigned->execute([$po_prod_id, $jci_main_id]);
        $result_sum = $stmt_sum_assigned->fetch(PDO::FETCH_ASSOC);
        $total_assigned_other_jci = (float)($result_sum['total_assigned'] ?? 0);

        // Disable quantity validation temporarily
        // if (!$id && ($total_assigned_other_jci + $assign_qty) > $auth_original_po_quantity) {
        //     throw new Exception("Total assigned quantity for '" . $auth_product_name . "' (item " . ($i + 1) . ") across all Job Cards exceeds original PO quantity (" . $auth_original_po_quantity . "). Currently assigned: " . $total_assigned_other_jci . ". Attempted assignment: " . $assign_qty . ".");
        // }


        if ($item_id && $item_id > 0) {
            $stmt_get_existing_jcn = $conn->prepare("SELECT job_card_number FROM jci_items WHERE id = ? AND jci_id = ?");
            $stmt_get_existing_jcn->execute([$item_id, $jci_main_id]);
            $existing_jcn_result = $stmt_get_existing_jcn->fetch(PDO::FETCH_ASSOC);
            $job_card_number_to_use = $existing_jcn_result ? $existing_jcn_result['job_card_number'] : $current_job_card_number;

            $stmt_item = $conn->prepare("UPDATE jci_items SET
                job_card_number = ?,
                po_product_id = ?,
                product_name = ?,
                item_code = ?,
                original_po_quantity = ?,
                quantity = ?,
                labour_cost = ?,
                total_amount = ?,
                delivery_date = ?,
                job_card_date = ?,
                job_card_type = ?,
                contracture_name = ?
                WHERE id = ? AND jci_id = ?"
            );
            $stmt_item->execute([
                $job_card_number_to_use,
                $po_prod_id,
                $auth_product_name,
                $auth_item_code,
                $auth_original_po_quantity,
                $assign_qty,
                $lab_cost,
                $item_total,
                $del_date,
                $item_jci_date,
                $item_jci_type,
                $contract_name,
                $item_id,
                $jci_main_id
            ]);
            $submitted_item_ids[] = $item_id;
        } else {
            $stmt_item = $conn->prepare("INSERT INTO jci_items (
                jci_id,
                job_card_number,
                po_product_id,
                product_name,
                item_code,
                original_po_quantity,
                quantity,
                labour_cost,
                total_amount,
                delivery_date,
                job_card_date,
                job_card_type,
                contracture_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_item->execute([
                $jci_main_id,
                $current_job_card_number,
                $po_prod_id,
                $auth_product_name,
                $auth_item_code,
                $auth_original_po_quantity,
                $assign_qty,
                $lab_cost,
                $item_total,
                $del_date,
                $item_jci_date,
                $item_jci_type,
                $contract_name
            ]);
            $submitted_item_ids[] = $conn->lastInsertId();
        }
    }

    if ($id) {
        if (!empty($submitted_item_ids)) {
            $placeholders = implode(',', array_fill(0, count($submitted_item_ids), '?'));
            $stmt_delete = $conn->prepare("DELETE FROM jci_items WHERE jci_id = ? AND id NOT IN ($placeholders)");
            $stmt_delete->execute(array_merge([$jci_main_id], $submitted_item_ids));
        } else {
            $stmt_delete_all = $conn->prepare("DELETE FROM jci_items WHERE jci_id = ?");
            $stmt_delete_all->execute([$jci_main_id]);
        }
    }

    // Update sell_order jci_created status
    $stmt_update_so = $conn->prepare("UPDATE sell_order SET jci_created = 1 WHERE id = ?");
    $stmt_update_so->execute([$sell_order_id]);
    
    // Update bom_main jci_assigned status
    if ($bom_id) {
        $stmt_update_bom = $conn->prepare("UPDATE bom_main SET jci_assigned = 1 WHERE id = ?");
        $stmt_update_bom->execute([$bom_id]);
    }
    
    // Update po_main jci_assigned status
    $stmt_update_po = $conn->prepare("UPDATE po_main SET jci_assigned = 1 WHERE id = ?");
    $stmt_update_po->execute([$po_id]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Job Card saved successfully!",
        'jci_id' => $jci_main_id,
        'new_base_jci_number' => generateBaseJCINumber($conn)
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => "Application error: " . $e->getMessage()
    ]);
} finally {
    if ($conn) {
        $conn = null;
    }
}

function generateBaseJCINumber($conn) {
    $year = date('Y');
    $prefix = "JCI-$year-";
    $stmt = $conn->prepare("SELECT MAX(
        CASE
            WHEN jci_number LIKE 'JOB-{$year}-%-%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(jci_number, '-', 3), '-', -1) AS UNSIGNED)
            WHEN jci_number LIKE 'JCI-{$year}-%' THEN CAST(SUBSTRING_INDEX(jci_number, '-', -1) AS UNSIGNED)
            ELSE 0
        END
    ) AS last_seq FROM jci_main
    WHERE jci_number LIKE 'JOB-{$year}-%' OR jci_number LIKE 'JCI-{$year}-%';");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $last_seq = (int)$result['last_seq'];
    $next_seq = $last_seq + 1;
    $seqFormatted = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
    return $prefix . $seqFormatted;
}

?>
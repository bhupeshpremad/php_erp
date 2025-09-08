<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');
global $conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = $_POST;

error_log("Received POST data: " . json_encode($data));

$conn->beginTransaction();

try {
    // Save multiple payments, one per payment in payments array
    $payments = json_decode($data['payments'] ?? '[]', true);
    $payment_ids = [];
    $jci_number = $data['jci_number'] ?? null;
    
    // Debug: Log received data
    error_log("Received payments data: " . $data['payments']);
    error_log("Decoded payments count: " . count($payments));
    error_log("JCI Number: " . $jci_number);
    
    // Check if this is an update (payment_id exists)
    $payment_main_id = null;
    if (!empty($data['payment_id'])) {
        $payment_main_id = $data['payment_id'];
        error_log("Updating existing payment with ID: $payment_main_id");
        // Update existing payment record
        $stmt_update = $conn->prepare("UPDATE payments SET updated_at = NOW() WHERE id = ?");
        $stmt_update->execute([$payment_main_id]);
    }

    // Insert single payment record for this JCI only if new
    if (!$payment_main_id) {
        error_log("Inserting new payment record for JCI: $jci_number");
        
        // Generate unique payment number
        $payment_number = 'PAY-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . time();
        
        $stmt = $conn->prepare("INSERT INTO payments (
            payment_number,
            jci_number,
            po_number,
            sell_order_number,
            payment_type,
            party_name,
            amount,
            payment_date,
            status,
            created_at,
            updated_at
        ) VALUES (
            :payment_number,
            :jci_number,
            :po_number,
            :sell_order_number,
            'made',
            :party_name,
            0,
            CURDATE(),
            'pending',
            NOW(),
            NOW()
        )");
        $stmt->execute([
            ':payment_number' => $payment_number,
            ':jci_number' => $jci_number,
            ':po_number' => $data['po_number'] ?? null,
            ':sell_order_number' => $data['sell_order_number'] ?? null,
            ':party_name' => 'JCI: ' . $jci_number
        ]);
        $payment_main_id = $conn->lastInsertId();
        error_log("New payment record inserted with ID: $payment_main_id");
    }

    // Insert only new payment_details records (skip existing ones)
    $inserted_payment_count = 0;
    foreach ($payments as $p) {
        error_log("Processing payment: " . json_encode($p));
        
        // Only process checked payments with required fields
        if (empty($p['cheque_type']) || empty($p['payment_date']) || empty($p['cheque_number'])) {
            error_log("Skipping incomplete payment: " . json_encode($p));
            continue; // Skip incomplete payments
        }
        
        $payment_category = $p['entity_type'] === 'job_card' ? 'Job Card' : 'Supplier';
        $existing_detail_id = null;
        $check_stmt = $conn->prepare("SELECT id FROM payment_details WHERE payment_id = ? AND cheque_number = ? AND payment_category = ?");
        $check_stmt->execute([$payment_main_id, $p['cheque_number'], $payment_category]);
        $existing_detail_id = $check_stmt->fetchColumn();

        if ($existing_detail_id) {
            error_log("Updating existing payment detail with ID: $existing_detail_id");
            $stmt = $conn->prepare("UPDATE payment_details SET
                jc_number = :jc_number,
                payment_type = :payment_type,
                cheque_number = :cheque_number,
                pd_acc_number = :pd_acc_number,
                ptm_amount = :ptm_amount,
                gst_percent = :gst_percent,
                gst_amount = :gst_amount,
                total_with_gst = :total_with_gst,
                payment_invoice_date = :payment_invoice_date,
                payment_date = :payment_date,
                payment_category = :payment_category,
                amount = :amount
            WHERE id = ?");
        } else {
            error_log("About to insert into payment_details with payment_id: $payment_main_id");
            $stmt = $conn->prepare("INSERT INTO payment_details (
                payment_id,
                jc_number,
                payment_type,
                cheque_number,
                pd_acc_number,
                ptm_amount,
                gst_percent,
                gst_amount,
                total_with_gst,
                payment_invoice_date,
                payment_date,
                payment_category,
                amount
            ) VALUES (
                :payment_id,
                :jc_number,
                :payment_type,
                :cheque_number,
                :pd_acc_number,
                :ptm_amount,
                :gst_percent,
                :gst_amount,
                :total_with_gst,
                :payment_invoice_date,
                :payment_date,
                :payment_category,
                :amount
            )");
        }

        // Get proper jc_number for job cards
        $jc_number_value = '';
        if ($p['entity_type'] === 'job_card') {
            $jc_number_value = $jci_number; // Use the main JCI number
        }

        // For suppliers, use invoice_number as cheque_number for matching purposes
        $cheque_number_value = trim($p['cheque_number'] ?? '');
        if (empty($cheque_number_value)) {
            $cheque_number_value = null; // Store as NULL if empty
        }

        if ($p['entity_type'] === 'supplier' && !empty($p['invoice_number'])) {
            $cheque_number_value = trim($p['invoice_number']);
            if (empty($cheque_number_value)) {
                $cheque_number_value = null; // Store as NULL if empty
            }
        }

        // Calculate total_with_gst properly
        $ptm_amount = floatval($p['ptm_amount'] ?? 0);
        $gst_percent = floatval($p['gst_percent'] ?? 0);
        $gst_amount = floatval($p['gst_amount'] ?? 0);
        $total_with_gst = floatval($p['total_with_gst'] ?? 0);

        // If total_with_gst is 0 or not provided, calculate it
        if ($total_with_gst <= 0) {
            $total_with_gst = $ptm_amount + $gst_amount;
        }

        $params = [
            ':payment_id' => $payment_main_id,
            ':jc_number' => $jc_number_value,
            ':payment_type' => $p['cheque_type'] ?? null,
            ':cheque_number' => $cheque_number_value,
            ':pd_acc_number' => $p['pd_acc_number'] ?? null,
            ':ptm_amount' => $ptm_amount,
            ':gst_percent' => $gst_percent,
            ':gst_amount' => $gst_amount,
            ':total_with_gst' => $total_with_gst,
            ':payment_invoice_date' => $p['invoice_date'] ?? null,
            ':payment_date' => $p['payment_date'] ?? null,
            ':payment_category' => $payment_category,
            ':amount' => $ptm_amount
        ];

        if ($existing_detail_id) {
            $params['id'] = $existing_detail_id;
            $stmt->execute(array_merge($params, ['id' => $existing_detail_id]));
            error_log("Payment detail updated with ID: $existing_detail_id, Result: " . ($stmt->rowCount() > 0 ? 'SUCCESS' : 'NO CHANGE'));
        } else {
            $stmt->execute($params);
            $inserted_id = $conn->lastInsertId();
            error_log("Payment detail inserted with ID: $inserted_id, Result: " . ($stmt->rowCount() > 0 ? 'SUCCESS' : 'FAILED'));
            $inserted_payment_count++;
        }
    }

    // Update JCI main table to mark payment as completed
    // This should only happen if ALL relevant payments for the JCI are marked as paid.
    // For now, let's keep it as is, but a more robust check might be needed.
    if ($jci_number) {
        error_log("Updating JCI main table for JCI: $jci_number");
        // Check if all approved purchase items linked to this JCI have corresponding payments
        $stmt_check_all_paid = $conn->prepare("
            SELECT COUNT(pi.id) AS total_approved_items,
                   COUNT(DISTINCT pd.cheque_number) AS paid_invoices
            FROM purchase_items pi
            JOIN purchase_main pm ON pi.purchase_main_id = pm.id
            LEFT JOIN payment_details pd ON pd.payment_id = (
                SELECT id FROM payments WHERE jci_number = pm.jci_number LIMIT 1
            ) AND pd.cheque_number = pi.invoice_number
            WHERE pm.jci_number = ?
        ");
        $stmt_check_all_paid->execute([$jci_number]);
        $payment_status = $stmt_check_all_paid->fetch(PDO::FETCH_ASSOC);

        if ($payment_status && $payment_status['total_approved_items'] > 0 && $payment_status['total_approved_items'] == $payment_status['paid_invoices']) {
            $stmt_update_jci = $conn->prepare("UPDATE jci_main SET payment_completed = 1 WHERE jci_number = ?");
            $stmt_update_jci->execute([$jci_number]);
            error_log("JCI $jci_number marked as payment completed.");
        } else {
            $stmt_update_jci = $conn->prepare("UPDATE jci_main SET payment_completed = 0 WHERE jci_number = ?");
            $stmt_update_jci->execute([$jci_number]);
            error_log("JCI $jci_number not yet fully payment completed (Approved items: " . $payment_status['total_approved_items'] . ", Paid invoices: " . $payment_status['paid_invoices'] . ").");
        }
    }

    $conn->commit();
    
    // Log successful save
    error_log("Payment saved successfully. Payment ID: $payment_main_id, JCI: $jci_number, Inserted payments: $inserted_payment_count");

    echo json_encode([
        'success' => true, 
        'message' => 'Payment saved successfully', 
        'payment_id' => $payment_main_id,
        'jci_number' => $jci_number // Pass back the JCI number
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Payment save failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save payment: ' . $e->getMessage()]);
}
?>

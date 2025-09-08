<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'job_cards' => [], 'suppliers' => []];

// Get user_type from POST data sent by the frontend (now explicitly passed by index.php)
$user_type = $_POST['user_type'] ?? 'guest';

if (isset($_GET['jci_number'])) {
    $jci_number = $_GET['jci_number'];

    try {
        // Fetch job card details from jci_main first
        $stmt_jci_main = $conn->prepare("SELECT id, jci_number, jci_type FROM jci_main WHERE jci_number = ?");
        $stmt_jci_main->execute([$jci_number]);
        $jci_main = $stmt_jci_main->fetch(PDO::FETCH_ASSOC);
        
        $job_cards = [];
        if ($jci_main) {
            // Try to get from jci_items first
            $stmt_jci = $conn->prepare("SELECT contracture_name, labour_cost, quantity, total_amount FROM jci_items WHERE jci_id = ?");
            $stmt_jci->execute([$jci_main['id']]);
            $job_card_items = $stmt_jci->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($job_card_items)) {
                foreach ($job_card_items as $item) {
                    $job_cards[] = [
                        'id' => $jci_main['id'],
                        'jci_number' => $jci_main['jci_number'],
                        'jci_type' => $jci_main['jci_type'],
                        'contracture_name' => $item['contracture_name'],
                        'labour_cost' => $item['labour_cost'],
                        'quantity' => $item['quantity'],
                        'total_amount' => $item['total_amount']
                    ];
                }
            } else {
                // Fallback: create default job card entry
                $job_cards[] = [
                    'id' => $jci_main['id'],
                    'jci_number' => $jci_main['jci_number'],
                    'jci_type' => $jci_main['jci_type'],
                    'contracture_name' => 'Default Contractor',
                    'labour_cost' => 0,
                    'quantity' => 1,
                    'total_amount' => 0
                ];
            }
        }

        $suppliers = [];

        // Fetch ALL purchase_main ids linked to this jci_number
        $stmt_purchase_main = $conn->prepare("SELECT pm.id FROM purchase_main pm JOIN jci_main jm ON pm.jci_number = jm.jci_number WHERE jm.jci_number = ?");
        $stmt_purchase_main->execute([$jci_number]);
        $purchase_main_ids = $stmt_purchase_main->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($purchase_main_ids)) {
            $all_approved_purchase_items = [];

            // Fetch ALL APPROVED purchase_items for each purchase_main_id
            $placeholders = implode(',', array_fill(0, count($purchase_main_ids), '?'));
            $stmt_approved_items = $conn->prepare("SELECT id, supplier_name, product_type, product_name as item_name, assigned_quantity as quantity, price, total as item_amount, invoice_number, date as invoice_date, amount as invoice_amount FROM purchase_items WHERE purchase_main_id IN ($placeholders)");
            $stmt_approved_items->execute($purchase_main_ids);
            $all_approved_purchase_items = $stmt_approved_items->fetchAll(PDO::FETCH_ASSOC);

            // Group items by supplier_name and invoice_number
            $grouped_suppliers = [];
            foreach ($all_approved_purchase_items as $item) {
                $supplier_name = $item['supplier_name'] ?? 'Unknown Supplier';
                $invoice_number = $item['invoice_number'] ?? ''; // Keep original invoice number
                $invoice_number_key = $invoice_number === null || $invoice_number === '' ? 'NO_INVOICE' : $invoice_number;

                $supplier_key = $supplier_name . '_' . $invoice_number_key;

                if (!isset($grouped_suppliers[$supplier_key])) {
                    $grouped_suppliers[$supplier_key] = [
                        'id' => md5($supplier_key),
                        'supplier_name' => $supplier_name,
                        'invoice_number' => $invoice_number ?: null,
                        'invoice_date' => $item['invoice_date'] ?? null,
                        'invoice_amount' => 0.0,
                        'items' => []
                    ];
                }

                $item_quantity = isset($item['quantity']) ? floatval($item['quantity']) : 0.0;
                $item_price = isset($item['price']) ? floatval($item['price']) : 0.0;
                $calculated_amount = $item_quantity * $item_price;

                $synthetic_id_source = $supplier_key . '|' . ($item['item_name'] ?? '') . '|' . ($item['product_type'] ?? '') . '|' . $item_quantity . '|' . $item_price;
                $grouped_suppliers[$supplier_key]['items'][] = [
                    'id' => isset($item['id']) && $item['id'] ? $item['id'] : md5($synthetic_id_source),
                    'item_name' => $item['item_name'],
                    'product_type' => $item['product_type'] ?? '',
                    'item_quantity' => $item_quantity,
                    'item_price' => $item_price,
                    'item_amount' => $calculated_amount,
                    // Removed item_approval_status as column doesn't exist
                ];

                // Prefer explicit invoice amount from DB when provided
                if (isset($item['invoice_amount']) && floatval($item['invoice_amount']) > 0) {
                    $grouped_suppliers[$supplier_key]['invoice_amount'] = floatval($item['invoice_amount']);
                } else {
                    // Otherwise, accumulate calculated amounts
                    $grouped_suppliers[$supplier_key]['invoice_amount'] += $calculated_amount;
                }
            }

            $suppliers = array_values($grouped_suppliers);
        }

        // Fetch PO Number and Sell Order Number for the Job Card Number
        $po_number = '';
        $sell_order_number = '';
        $stmt_po = $conn->prepare("SELECT po_number, sell_order_number FROM po_main WHERE id = (SELECT po_id FROM jci_main WHERE jci_number = ? LIMIT 1) LIMIT 1");
        $stmt_po->execute([$jci_number]);
        $po_data = $stmt_po->fetch(PDO::FETCH_ASSOC);
        if ($po_data) {
            $po_number = $po_data['po_number'];
            $sell_order_number = $po_data['sell_order_number'];
        }

        // Calculate po_amount as sum of total_amount from po_items for the PO linked to this job card
        $po_amount = 0;
        $stmt_po_amt = $conn->prepare("SELECT SUM(total_amount) as po_amount FROM po_items WHERE po_id = (SELECT po_id FROM jci_main WHERE jci_number = ? LIMIT 1)");
        $stmt_po_amt->execute([$jci_number]);
        $po_amt_result = $stmt_po_amt->fetch(PDO::FETCH_ASSOC);
        if ($po_amt_result && isset($po_amt_result['po_amount'])) {
            $po_amount = floatval($po_amt_result['po_amount']);
        }

        // For soa_number, set equal to po_amount as fallback
        $soa_number = $po_amount;

        // Fetch existing payments for this job card with supplier names
        // This query also needs to consider item_approval_status if we're linking payments to approved items
        // For simplicity now, we assume if a payment exists, it was for an approved item.
        // If stricter linkage is needed, we would need to add a join to purchase_items and filter there.
        $stmt_existing_payments = $conn->prepare("
            SELECT 
                p.id as payment_id, 
                p.jci_number, 
                pd.jc_number, 
                pd.payment_type, 
                pd.cheque_number, 
                pd.pd_acc_number, 
                pd.ptm_amount, 
                pd.payment_invoice_date, 
                pd.payment_date, 
                pd.payment_category
            FROM payments p
            LEFT JOIN payment_details pd ON p.id = pd.payment_id
            WHERE p.jci_number = ?
        ");
        $stmt_existing_payments->execute([$jci_number]);
        $existing_payments = $stmt_existing_payments->fetchAll(PDO::FETCH_ASSOC);

        // Resolve entity_name in PHP
        foreach ($existing_payments as &$payment) {
            $payment['entity_name'] = 'Unknown'; // Default
            if ($payment['payment_category'] === 'Supplier') {
                $stmt_supplier_name = $conn->prepare("SELECT pi.supplier_name FROM purchase_items pi JOIN purchase_main pm ON pi.purchase_main_id = pm.id WHERE pm.jci_number = ? AND pi.invoice_number = ? LIMIT 1");
                $stmt_supplier_name->execute([$jci_number, $payment['cheque_number']]);
                $supplier_name = $stmt_supplier_name->fetchColumn();
                if ($supplier_name) {
                    $payment['entity_name'] = $supplier_name;
                }
            } elseif ($payment['payment_category'] === 'Job Card') {
                $stmt_contracture_name = $conn->prepare("SELECT ji.contracture_name FROM jci_items ji JOIN jci_main jm ON ji.jci_id = jm.id WHERE jm.jci_number = ? LIMIT 1");
                $stmt_contracture_name->execute([$jci_number]);
                $contracture_name = $stmt_contracture_name->fetchColumn();
                if ($contracture_name) {
                    $payment['entity_name'] = $contracture_name;
                }
            }
        }
        unset($payment); // Unset reference to avoid unexpected behavior

        // Fetch invoice_number and invoice_date from purchase_items table for the job card
        // Only fetch from approved items
        $stmt_invoice = $conn->prepare("SELECT DISTINCT pi.invoice_number, pi.date as invoice_date FROM purchase_items pi JOIN purchase_main pm ON pi.purchase_main_id = pm.id WHERE pm.jci_number = ? LIMIT 1");
        $stmt_invoice->execute([$jci_number]);
        $invoice_data = $stmt_invoice->fetch(PDO::FETCH_ASSOC);
        $invoice_number = $invoice_data['invoice_number'] ?? null;
        $invoice_date = $invoice_data['invoice_date'] ?? null;

        $response['success'] = true;
        $response['job_cards'] = $job_cards;
        $response['suppliers'] = $suppliers;
        $response['po_number'] = $po_number;
        $response['sell_order_number'] = $sell_order_number;
        $response['po_amount'] = $po_amount;
        $response['soa_number'] = $soa_number;
        $response['existing_payments'] = $existing_payments;
        
        // Add payment status for each supplier
        foreach ($suppliers as &$supplier) {
            $supplier['is_paid'] = false;
            foreach ($existing_payments as $payment) {
                if ($payment['payment_category'] === 'Supplier' && 
                    $payment['entity_name'] === $supplier['supplier_name'] &&
                    $payment['cheque_number'] === $supplier['invoice_number'] &&
                    !empty($payment['payment_date'])) {
                    $supplier['is_paid'] = true;
                    break;
                }
            }
        }
        unset($supplier);
        $response['invoice_number'] = $invoice_number;
        $response['invoice_date'] = $invoice_date;
    } catch (Exception $e) {
        $response['message'] = 'Error fetching job card details: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Job Card Number (jci_number) not provided.';
}

echo json_encode($response);
?>

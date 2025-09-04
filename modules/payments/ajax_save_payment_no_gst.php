<?php

include_once '../../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conn;

    if (!$conn instanceof PDO) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection not established.']);
        exit;
    }

    $payment_id = isset($_POST['payment_id']) && $_POST['payment_id'] !== '' ? intval($_POST['payment_id']) : 0;

    $pon_number = htmlspecialchars(trim($_POST['pon_number'] ?? ''));
    $po_amt = filter_var($_POST['po_amt'] ?? 0, FILTER_VALIDATE_FLOAT);
    $son_number = htmlspecialchars(trim($_POST['son_number'] ?? ''));
    $soa_number = filter_var($_POST['soa_number'] ?? 0, FILTER_VALIDATE_FLOAT);

    $job_cards_json = $_POST['job_cards'] ?? '[]';
    $job_cards = json_decode($job_cards_json, true);

    $suppliers_json = $_POST['suppliers'] ?? '[]';
    $suppliers = json_decode($suppliers_json, true);

    $payments_json = $_POST['payments'] ?? '[]';
    $payments = json_decode($payments_json, true);

    if ($po_amt === false || empty($son_number) || $soa_number === false) {
        $response['message'] = 'Required PO Information fields are missing or invalid.';
        echo json_encode($response);
        exit;
    }

    try {
        $conn->beginTransaction();

        if ($payment_id > 0) {
            $stmt = $conn->prepare("UPDATE payments SET
                pon_number = ?, po_amt = ?, son_number = ?, soa_number = ?
                WHERE id = ?");

            $stmt->execute([
                $pon_number, $po_amt, $son_number, $soa_number, $payment_id
            ]);

            // Delete existing related records
            $conn->prepare("DELETE FROM job_cards WHERE payment_id = ?")->execute([$payment_id]);
            $conn->prepare("DELETE supplier_items FROM supplier_items INNER JOIN suppliers ON supplier_items.supplier_id = suppliers.id WHERE suppliers.payment_id = ?")->execute([$payment_id]);
            $conn->prepare("DELETE FROM suppliers WHERE payment_id = ?")->execute([$payment_id]);
            $conn->prepare("DELETE FROM payment_details WHERE payment_id = ?")->execute([$payment_id]);

        } else {
            $stmt = $conn->prepare("INSERT INTO payments (
                pon_number, po_amt, son_number, soa_number
            ) VALUES (?, ?, ?, ?)");

            $stmt->execute([
                $pon_number, $po_amt, $son_number, $soa_number
            ]);
            $payment_id = $conn->lastInsertId();
        }

        // Insert job cards
        $stmt_job_card = $conn->prepare("INSERT INTO job_cards (payment_id, jc_number, jc_type, contracture_name, labour_cost, quantity, total_amount, jc_amt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($job_cards as $job_card) {
            $jc_number = htmlspecialchars(trim($job_card['jc_number'] ?? ''));
            $jc_type = htmlspecialchars(trim($job_card['jc_type'] ?? ''));
            $contracture_name = htmlspecialchars(trim($job_card['contracture_name'] ?? ''));
            $labour_cost = filter_var($job_card['labour_cost'] ?? 0, FILTER_VALIDATE_FLOAT);
            $quantity = filter_var($job_card['quantity'] ?? 0, FILTER_VALIDATE_INT);
            $total_amount = filter_var($job_card['total_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
            $jc_amt = filter_var($job_card['jc_amt'] ?? 0, FILTER_VALIDATE_FLOAT);
            if (empty($jc_number) || $jc_amt === false) {
                continue;
            }
            $stmt_job_card->execute([$payment_id, $jc_number, $jc_type, $contracture_name, $labour_cost, $quantity, $total_amount, $jc_amt]);
        }

        // Insert suppliers and supplier items
        $stmt_supplier = $conn->prepare("INSERT INTO suppliers (payment_id, supplier_name, invoice_number, invoice_date, invoice_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt_supplier_item = $conn->prepare("INSERT INTO supplier_items (supplier_id, item_name, item_quantity, item_price, item_amount) VALUES (?, ?, ?, ?, ?)");

        foreach ($suppliers as $supplier) {
            $supplier_name = htmlspecialchars(trim($supplier['name'] ?? ''));
            $invoice_number = htmlspecialchars(trim($supplier['invoice_number'] ?? ''));
            $invoice_date = htmlspecialchars(trim($supplier['invoice_date'] ?? ''));
            $invoice_amount = filter_var($supplier['invoice_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
            if (empty($supplier_name) || empty($invoice_number) || $invoice_amount === false) {
                continue;
            }
            $stmt_supplier->execute([$payment_id, $supplier_name, $invoice_number, $invoice_date, $invoice_amount]);
            $supplier_id = $conn->lastInsertId();

            if (!empty($supplier['items']) && is_array($supplier['items'])) {
                foreach ($supplier['items'] as $item) {
                    $item_name = htmlspecialchars(trim($item['name'] ?? ''));
                    $item_quantity = filter_var($item['quantity'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $item_price = filter_var($item['price'] ?? 0, FILTER_VALIDATE_FLOAT);
                    $item_amount = filter_var($item['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
                    if (empty($item_name) || $item_quantity === false || $item_price === false || $item_amount === false) {
                        continue;
                    }
                    $stmt_supplier_item->execute([$supplier_id, $item_name, $item_quantity, $item_price, $item_amount]);
                }
            }
        }

        // Insert payment details without GST columns
        $stmt_payment_detail = $conn->prepare("INSERT INTO payment_details (payment_id, payment_category, payment_type, cheque_number, pd_acc_number, payment_full_partial, ptm_amount, payment_invoice_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($payments as $payment) {
            $payment_category = $payment['payment_category'] ?? 'Job Card';
            $payment_type = htmlspecialchars(trim($payment['payment_type'] ?? ''));
            $cheque_number = htmlspecialchars(trim($payment['cheque_number'] ?? ''));
            $pd_acc_number = htmlspecialchars(trim($payment['pd_acc_number'] ?? ''));
            $payment_full_partial = $payment['payment_full_partial'] ?? '';
            $ptm_amount = filter_var($payment['ptm_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
            $payment_invoice_date = $payment['payment_invoice_date'] ?? '';

            if (empty($payment_type) || empty($pd_acc_number) || empty($payment_full_partial) || $ptm_amount === false || empty($payment_invoice_date)) {
                continue;
            }
            $stmt_payment_detail->execute([$payment_id, $payment_category, $payment_type, $cheque_number, $pd_acc_number, $payment_full_partial, $ptm_amount, $payment_invoice_date]);
        }

        $conn->commit();

        $response['success'] = true;
        $response['message'] = $payment_id > 0 ? 'Payment updated successfully.' : 'Payment added successfully.';
        $response['payment_id'] = $payment_id;

    } catch (Exception $e) {
        $conn->rollBack();
        error_log('Error saving payment: ' . $e->getMessage());
        $response['message'] = 'Error saving payment: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>

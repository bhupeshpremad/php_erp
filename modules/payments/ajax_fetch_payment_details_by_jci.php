<?php
include_once '../../config/config.php';
header('Content-Type: application/json');
global $conn;

$response = ['success' => false, 'payment_details' => []];

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];
    
    try {
        // First get the JCI number from payments table
        $stmt_payment = $conn->prepare("SELECT jci_number FROM payments WHERE id = ?");
        $stmt_payment->execute([$payment_id]);
        $payment_info = $stmt_payment->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment_info) {
            throw new Exception('Payment not found');
        }
        
        $jci_number = $payment_info['jci_number'];
        
        // Get all unique suppliers by invoice number for this JCI with image data
        $stmt_all_suppliers = $conn->prepare("
            SELECT pi.supplier_name, pi.invoice_number, pi.amount as invoice_amount, 
                   pi.invoice_image, pi.builty_image
            FROM purchase_items pi 
            JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
            WHERE pm.jci_number = ? AND pi.invoice_number IS NOT NULL AND pi.invoice_number != ''
            GROUP BY pi.supplier_name, pi.invoice_number
        ");
        $stmt_all_suppliers->execute([$jci_number]);
        $suppliers_data = $stmt_all_suppliers->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch payment details from payment_details table
        $stmt = $conn->prepare(
            "SELECT 
                id, 
                payment_category, 
                payment_type, 
                cheque_number, 
                pd_acc_number, 
                ptm_amount, 
                payment_date, 
                payment_invoice_date, 
                jc_number
            FROM payment_details 
            WHERE payment_id = ?"
        );
        $stmt->execute([$payment_id]);
        $payment_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get supplier names from purchase_items for this JCI
        $supplier_names = [];
        $stmt_suppliers = $conn->prepare("
            SELECT DISTINCT pi.supplier_name, pi.invoice_number 
            FROM purchase_items pi 
            JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
            WHERE pm.jci_number = ?
        ");
        $stmt_suppliers->execute([$jci_number]);
        $suppliers = $stmt_suppliers->fetchAll(PDO::FETCH_ASSOC);
        
        // Create a mapping of supplier names
        foreach ($suppliers as $supplier) {
            $supplier_names[$supplier['supplier_name']] = $supplier['supplier_name'];
        }
        
        // Get contractor names from jci_items for this JCI
        $contractor_names = [];
        $stmt_contractors = $conn->prepare("
            SELECT DISTINCT ji.contracture_name 
            FROM jci_items ji 
            JOIN jci_main jm ON ji.jci_id = jm.id 
            WHERE jm.jci_number = ?
        ");
        $stmt_contractors->execute([$jci_number]);
        $contractors = $stmt_contractors->fetchAll(PDO::FETCH_ASSOC);

        // Debug: log what we found
        error_log("Payment ID: $payment_id, JCI: $jci_number, Payment details count: " . count($payment_details));
        
        // Enhance payment details with supplier/contractor names and status
        foreach ($payment_details as &$detail) {
            if (!isset($detail['supplier_name'])) {
                $detail['supplier_name'] = '';
            }
            if (!isset($detail['contracture_name'])) {
                $detail['contracture_name'] = '';
            }
            
            // Set status based on payment_date and cheque_number
            if (!empty($detail['payment_date']) && !empty($detail['cheque_number'])) {
                $detail['status'] = 'paid';
            } else {
                $detail['status'] = 'pending';
            }

            if ($detail['payment_category'] === 'Job Card') {
                // For job card payments, try to get contractor name
                if (!empty($contractors)) {
                    $detail['contracture_name'] = $contractors[0]['contracture_name'] ?? 'Job Card Payment';
                } else {
                    $detail['contracture_name'] = 'Job Card Payment';
                }
            } elseif ($detail['payment_category'] === 'Supplier' && empty($detail['supplier_name'])) {
                // For supplier payments, get supplier name from cheque_number (invoice_number)
                $stmt_supplier = $conn->prepare("
                    SELECT pi.supplier_name 
                    FROM purchase_items pi 
                    JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
                    WHERE pm.jci_number = ? AND pi.invoice_number = ?
                    LIMIT 1
                ");
                $stmt_supplier->execute([$jci_number, $detail['cheque_number']]);
                $supplier_result = $stmt_supplier->fetch(PDO::FETCH_ASSOC);
                
                $detail['supplier_name'] = $supplier_result['supplier_name'] ?? 'Unknown Supplier';
            }
        }
        unset($detail); // break reference

        // Only add suppliers that have actual payments (not pending ones) to the modal
        // Update existing payments with image data from suppliers_data
        foreach ($payment_details as &$detail) {
            if ($detail['payment_category'] === 'Supplier') {
                // Find matching supplier data for images
                foreach ($suppliers_data as $supplier) {
                    if ($detail['supplier_name'] === $supplier['supplier_name'] &&
                        $detail['cheque_number'] === $supplier['invoice_number']) {
                        $detail['invoice_image'] = $supplier['invoice_image'] ?? '';
                        $detail['builty_image'] = $supplier['builty_image'] ?? '';
                        $detail['invoice_number'] = $supplier['invoice_number'];
                        break;
                    }
                }
            }
        }
        unset($detail);
        
        $response['success'] = true;
        $response['payment_details'] = $payment_details;
        
    } catch (Exception $e) {
        $response['message'] = 'Error fetching payment details: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Payment ID not provided.';
}

echo json_encode($response);
?>

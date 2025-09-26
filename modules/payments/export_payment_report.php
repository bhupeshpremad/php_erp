<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

use Mpdf\Mpdf;

// Check if payment_id is provided
$payment_id = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;
if ($payment_id <= 0) {
    http_response_code(400);
    echo 'Invalid payment id: ' . $payment_id;
    exit;
}

global $conn;

try {
    // Fetch core payment info
    $stmt = $conn->prepare("SELECT id, jci_number, po_number, sell_order_number, created_at FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$payment) {
        http_response_code(404);
        echo 'Payment not found for ID: ' . $payment_id;
        exit;
    }

    $jci_number = $payment['jci_number'];

    // Fetch BOM details via JCI → PO → BOM if available
    $stmtBom = $conn->prepare("SELECT bm.bom_number, bm.client_name, bm.prepared_by, bm.order_date
        FROM bom_main bm
        JOIN jci_main jm ON jm.bom_id = bm.id
        WHERE jm.jci_number = ? LIMIT 1");
    $stmtBom->execute([$jci_number]);
    $bom = $stmtBom->fetch(PDO::FETCH_ASSOC);

    // Suppliers and purchased items for this JCI
    $stmtItems = $conn->prepare("SELECT 
            pi.supplier_name,
            pi.product_type,
            pi.product_name,
            pi.assigned_quantity AS qty,
            pi.price,
            pi.total AS amount,
            pi.invoice_number,
            pm.jci_number
        FROM purchase_items pi
        JOIN purchase_main pm ON pm.id = pi.purchase_main_id
        WHERE pm.jci_number = ?");
    $stmtItems->execute([$jci_number]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Job cards and amounts
    $stmtJci = $conn->prepare("SELECT ji.job_card_number, ji.product_name, ji.quantity, ji.total_amount
        FROM jci_items ji
        JOIN jci_main jm ON jm.id = ji.jci_id
        WHERE jm.jci_number = ?");
    $stmtJci->execute([$jci_number]);
    $jobcards = $stmtJci->fetchAll(PDO::FETCH_ASSOC);

    // Get SO (Sales Order) total amount from po_items
    $soTotal = 0;
    if (!empty($payment['po_number'])) {
        $stmtSo = $conn->prepare("SELECT SUM(total_amount) as so_total FROM po_items pi 
            JOIN po_main pm ON pm.id = pi.po_id 
            WHERE pm.po_number = ?");
        $stmtSo->execute([$payment['po_number']]);
        $soResult = $stmtSo->fetch(PDO::FETCH_ASSOC);
        $soTotal = (float)($soResult['so_total'] ?? 0);
    }

    // Calculate totals
    $grandSupplier = 0; foreach ($items as $it) { $grandSupplier += (float)($it['amount'] ?? 0); }
    $grandJc = 0; foreach ($jobcards as $jc) { $grandJc += (float)($jc['total_amount'] ?? 0); }
    $grandTotal = $grandSupplier + $grandJc;

    // Calculate Profit/Loss
    $profitLoss = $soTotal - $grandTotal;
    $profitLossPercentage = $soTotal > 0 ? ($profitLoss / $soTotal) * 100 : 0;
    $isProfitable = $profitLoss >= 0;

    // Build HTML for PDF
    $html = '<h2 style="margin:0;">Payment Report</h2>';
    $html .= '<p style="margin:0 0 10px 0;">Payment ID: ' . htmlspecialchars($payment_id) . ' | JCI: ' . htmlspecialchars($jci_number) . ' | PO: ' . htmlspecialchars($payment['po_number'] ?? '') . ' | SO: ' . htmlspecialchars($payment['sell_order_number'] ?? '') . '</p>';

    // BOM summary
    $html .= '<h3 style="margin:10px 0 5px 0;">BOM Details</h3>';
    if ($bom) {
        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">'
            . '<tr><td><strong>BOM Number</strong></td><td>' . htmlspecialchars($bom['bom_number']) . '</td>'
            . '<td><strong>Client</strong></td><td>' . htmlspecialchars($bom['client_name']) . '</td></tr>'
            . '<tr><td><strong>Prepared By</strong></td><td>' . htmlspecialchars($bom['prepared_by']) . '</td>'
            . '<td><strong>Order Date</strong></td><td>' . htmlspecialchars($bom['order_date']) . '</td></tr>'
            . '</table>';
    } else {
        $html .= '<p>No BOM linked.</p>';
    }

    // Suppliers and purchased items
    $html .= '<h3 style="margin:15px 0 5px 0;">Suppliers & Purchases</h3>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">'
        . '<thead><tr>'
        . '<th>Supplier</th><th>Product Type</th><th>Item</th><th>Qty</th><th>Price</th><th>Amount</th><th>Invoice</th>'
        . '</tr></thead><tbody>';
    if (!empty($items)) {
        foreach ($items as $it) {
            $html .= '<tr>'
                . '<td>' . htmlspecialchars($it['supplier_name']) . '</td>'
                . '<td>' . htmlspecialchars($it['product_type']) . '</td>'
                . '<td>' . htmlspecialchars($it['product_name']) . '</td>'
                . '<td style="text-align:right;">' . number_format((float)$it['qty'], 2) . '</td>'
                . '<td style="text-align:right;">' . number_format((float)$it['price'], 2) . '</td>'
                . '<td style="text-align:right;">' . number_format((float)$it['amount'], 2) . '</td>'
                . '<td>' . htmlspecialchars($it['invoice_number'] ?? '') . '</td>'
                . '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="7" style="text-align:center;">No purchase items found.</td></tr>';
    }
    $html .= '</tbody></table>';

    // Job cards
    $html .= '<h3 style="margin:15px 0 5px 0;">Job Cards</h3>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">'
        . '<thead><tr>'
        . '<th>Job Card Number</th><th>Item</th><th>Qty</th><th>Amount</th>'
        . '</tr></thead><tbody>';
    if (!empty($jobcards)) {
        foreach ($jobcards as $jc) {
            $html .= '<tr>'
                . '<td>' . htmlspecialchars($jc['job_card_number']) . '</td>'
                . '<td>' . htmlspecialchars($jc['product_name'] ?? '') . '</td>'
                . '<td style="text-align:right;">' . number_format((float)($jc['quantity'] ?? 0), 2) . '</td>'
                . '<td style="text-align:right;">' . number_format((float)($jc['total_amount'] ?? 0), 2) . '</td>'
                . '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="4" style="text-align:center;">No job cards found.</td></tr>';
    }
    $html .= '</tbody></table>';

    // Totals and Profit/Loss Analysis
    $html .= '<h3 style="margin:15px 0 5px 0;">Financial Summary</h3>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">'
        . '<tr><td><strong>SO (Sales Order) Total</strong></td><td style="text-align:right;">' . number_format($soTotal, 2) . '</td></tr>'
        . '<tr><td><strong>Suppliers Total</strong></td><td style="text-align:right;">' . number_format($grandSupplier, 2) . '</td></tr>'
        . '<tr><td><strong>Job Cards Total</strong></td><td style="text-align:right;">' . number_format($grandJc, 2) . '</td></tr>'
        . '<tr><td><strong>Total Expenses</strong></td><td style="text-align:right;">' . number_format($grandTotal, 2) . '</td></tr>'
        . '<tr style="background-color:' . ($isProfitable ? '#d4edda' : '#f8d7da') . ';">'
        . '<td><strong>' . ($isProfitable ? 'Profit' : 'Loss') . '</strong></td>'
        . '<td style="text-align:right; color:' . ($isProfitable ? 'green' : 'red') . ';"><strong>' 
        . ($isProfitable ? '+' : '') . number_format($profitLoss, 2) . '</strong></td></tr>'
        . '<tr><td><strong>Profit/Loss Percentage</strong></td>'
        . '<td style="text-align:right; color:' . ($isProfitable ? 'green' : 'red') . ';"><strong>' 
        . ($isProfitable ? '+' : '') . number_format($profitLossPercentage, 2) . '%</strong></td></tr>'
        . '</table>';

    // Add profit/loss analysis section
    $html .= '<h3 style="margin:15px 0 5px 0;">Profit/Loss Analysis</h3>';
    $html .= '<div style="padding:10px; border:1px solid #ccc; background-color:' . ($isProfitable ? '#d4edda' : '#f8d7da') . ';">';
    if ($isProfitable) {
        $html .= '<p><strong>✓ PROFITABLE PROJECT</strong></p>';
        $html .= '<p>This project generated a profit of <strong>' . number_format($profitLoss, 2) . '</strong> (' . number_format($profitLossPercentage, 2) . '% margin).</p>';
    } else {
        $html .= '<p><strong>⚠ LOSS-MAKING PROJECT</strong></p>';
        $html .= '<p>This project incurred a loss of <strong>' . number_format(abs($profitLoss), 2) . '</strong> (' . number_format(abs($profitLossPercentage), 2) . '% loss).</p>';
    }
    $html .= '<p><strong>Breakdown:</strong></p>';
    $html .= '<ul>';
    $html .= '<li>Revenue (SO): ' . number_format($soTotal, 2) . '</li>';
    $html .= '<li>Supplier Costs: ' . number_format($grandSupplier, 2) . '</li>';
    $html .= '<li>Job Card Costs: ' . number_format($grandJc, 2) . '</li>';
    $html .= '<li>Total Costs: ' . number_format($grandTotal, 2) . '</li>';
    $html .= '</ul>';
    $html .= '</div>';

    // Create PDF
    $mpdf = new Mpdf([
        'tempDir' => sys_get_temp_dir(),
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);
    
    $mpdf->SetTitle('Payment Report - ' . $jci_number);
    $mpdf->WriteHTML($html);
    
    // Set headers for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $jci_number . '_payment_report.pdf"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $mpdf->Output($jci_number . '_payment_report.pdf', 'D');
    exit;

} catch (Exception $e) {
    error_log("PDF generation error: " . $e->getMessage());
    http_response_code(500);
    echo 'Error generating PDF: ' . $e->getMessage();
    exit;
}
?>



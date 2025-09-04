<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (ob_get_length()) {
    ob_end_clean();
}

require __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_GET['po_id']) || empty($_GET['po_id'])) {
    die('PO ID is required');
}

$po_id = intval($_GET['po_id']);
global $conn;

try {
    // Fetch PO main data
    $stmt = $conn->prepare("SELECT * FROM po_main WHERE id = :po_id");
    $stmt->execute(['po_id' => $po_id]);
    $po_main = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$po_main) {
        die('PO not found');
    }

    // Fetch PO items
    $stmt = $conn->prepare("SELECT * FROM po_items WHERE po_id = :po_id ORDER BY id ASC");
    $stmt->execute(['po_id' => $po_id]);
    $po_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize mPDF
    $mpdf = new \Mpdf\Mpdf([
        'orientation' => 'P',
        'tempDir' => __DIR__ . '/../../../tmp',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    // HTML content for PDF with professional layout
    $html = '<html><head><style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; }
        .company-box { border: 1px solid #000; padding: 8px; margin-bottom: 5px; }
        .voucher-box { border: 1px solid #000; padding: 5px; }
        .section-box { border: 1px solid #000; padding: 8px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; font-size: 9px; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .total-row { font-weight: bold; }
        .signature-section { margin-top: 20px; }
    </style></head><body>';

    $html .= '<div class="header">PURCHASE ORDER</div>';
    
    // Two column layout
    $html .= '<table style="border: none;"><tr><td style="width: 50%; border: none; vertical-align: top;">';
    
    // Invoice To section
    $html .= '<div class="company-box">';
    $html .= '<strong>Invoice To</strong><br>';
    $html .= 'PUREWOOD<br>';
    $html .= 'G-178, Boranada Industrial Park<br>';
    $html .= 'Jodhpur - 342012, Rajasthan<br>';
    $html .= 'GSTIN : AAQFP4054K1ZQ<br>';
    $html .= 'State Name : RAJASTHAN, Code : 08<br>';
    $html .= 'E-Mail : info@purewood.in';
    $html .= '</div>';
    
    $html .= '</td><td style="width: 50%; border: none; vertical-align: top;">';
    
    // Voucher details
    $html .= '<table class="voucher-box">';
    $html .= '<tr><td>Voucher No.</td><td>' . htmlspecialchars($po_main['po_number']) . '</td></tr>';
    $html .= '<tr><td>Dated</td><td>' . date('d-M-y', strtotime($po_main['created_at'])) . '</td></tr>';
    $html .= '<tr><td>Mode/Terms of Payment</td><td></td></tr>';
    $html .= '<tr><td>Reference No. & Date</td><td></td></tr>';
    $html .= '<tr><td>Other References</td><td></td></tr>';
    $html .= '</table>';
    
    $html .= '</td></tr></table>';
    
    // Consignee and dispatch details
    $html .= '<table style="border: none;"><tr><td style="width: 50%; border: none; vertical-align: top;">';
    $html .= '<div class="section-box"><strong>Consignee (Ship to)</strong><br><br><br></div>';
    $html .= '</td><td style="width: 50%; border: none; vertical-align: top;">';
    $html .= '<table class="voucher-box">';
    $html .= '<tr><td>Dispatched through</td><td></td></tr>';
    $html .= '<tr><td>Destination</td><td></td></tr>';
    $html .= '<tr><td>Terms of Delivery</td><td></td></tr>';
    $html .= '</table>';
    $html .= '</td></tr></table>';
    
    // Supplier section
    $html .= '<div class="section-box"><strong>Supplier (Bill from)</strong><br><br></div>';
    
    // Items table
    if (!empty($po_items)) {
        $html .= '<table>';
        $html .= '<tr><th>Sl No</th><th>Description of Goods</th><th>Due on</th><th>Quantity</th><th>Rate</th><th>per</th><th>Amount</th></tr>';
        $serial = 1;
        $total_amount = 0;
        foreach ($po_items as $item) {
            $item_total = floatval($item['total_amount'] ?? (floatval($item['quantity']) * floatval($item['price'])));
            $total_amount += $item_total;
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $serial++ . '</td>';
            $html .= '<td>' . htmlspecialchars($item['product_name'] ?? '') . '</td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align: center;">' . htmlspecialchars($item['quantity'] ?? '') . '</td>';
            $html .= '<td style="text-align: right;">' . number_format(floatval($item['price'] ?? 0), 2) . '</td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align: right;">' . number_format($item_total, 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '<tr class="total-row"><td colspan="6" style="text-align: right;">Total</td><td style="text-align: right;">' . number_format($total_amount, 2) . '</td></tr>';
        $html .= '</table>';
    }
    
    // Footer
    $html .= '<div style="text-align: right; margin-top: 10px;">E. & O.E</div>';
    
    // Signature section
    $html .= '<div class="signature-section">';
    $html .= '<table style="border: none;"><tr>';
    $html .= '<td style="border: none;">Company\'s PAN : AAQFP4054K</td>';
    $html .= '<td style="border: none; text-align: right;">for PUREWOOD</td>';
    $html .= '</tr><tr>';
    $html .= '<td style="border: none;"></td>';
    $html .= '<td style="border: none; text-align: right; padding-top: 30px;">Authorised Signatory</td>';
    $html .= '</tr></table>';
    $html .= '</div>';
    
    $html .= '</body></html>';
    
    // Write HTML to mPDF and output
    $mpdf->WriteHTML($html);
    
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    $filename = htmlspecialchars($po_main['po_number']) . '.pdf';
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $mpdf->Output();
    exit;
    
} catch (Exception $e) {
    if (ob_get_length()) {
        ob_end_clean();
    }
    exit('Error generating PDF: ' . $e->getMessage());
}
?>
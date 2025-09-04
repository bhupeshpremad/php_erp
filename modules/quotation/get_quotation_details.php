<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (ob_get_length()) ob_end_clean();

require __DIR__ . '/../../vendor/autoload.php';

use \Mpdf\Mpdf;

include_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid quotation ID');
}

$quotationId = intval($_GET['id']);

try {
    global $conn;

    if (!$conn) {
        exit('Database connection not established.');
    }

    // Fetch quotation data
    $stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ?");
    $stmt->execute([$quotationId]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quotation) {
        exit('Quotation not found');
    }

    // Fetch products for this quotation
    $stmt2 = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = ?");
    $stmt2->execute([$quotationId]);
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Create new PDF document
    $mpdf = new Mpdf();

    // Build HTML content for PDF
    $html = '<h1 style="text-align:center;">PUREWOOD</h1>';

    $html .= '<h3>Seller Details</h3>';
    $html .= '<p>Purewood<br>
    G178 Special Economy Area (SEZ)<br>
    Export Promotion Industrial Park (EPIP)<br>
    Boranada, Jodhpur, Rajasthan<br>
    India (342001) GST: 08AAQFP4054K1ZQ</p>';

    $html .= '<h3>Buyer Details</h3>';
    $html .= '<p>' . htmlspecialchars($quotation['customer_name'] ?? '') . '<br>' .
        htmlspecialchars($quotation['customer_address'] ?? '') . '<br>' .
        htmlspecialchars($quotation['customer_city'] ?? '') . '<br>' .
        htmlspecialchars($quotation['customer_state'] ?? '') . '</p>';

    $html .= '<p><strong>Payment Terms:</strong> ' . htmlspecialchars($quotation['delivery_term'] ?? '60 Days') . '<br>';
    $html .= '<strong>Terms of Delivery:</strong> ' . htmlspecialchars($quotation['terms_of_delivery'] ?? 'FOB') . '<br>';
    $html .= '<strong>Payment Term:</strong> ' . htmlspecialchars($quotation['payment_term'] ?? '30% advance 70% on DOCS') . '</p>';

    $html .= '<p><strong>Quote Number:</strong> ' . htmlspecialchars($quotation['quotation_number'] ?? '') . '<br>';
    $html .= '<strong>Date of Quote Raised:</strong> ' . htmlspecialchars($quotation['quotation_date'] ?? '') . '</p>';

    // Table header
    $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $html .= '<tr>
        <th>S.No.</th>
        <th>Item Name</th>
        <th>Item Code</th>
        <th>Qty</th>
        <th>Unit</th>
        <th>Rate</th>
        <th>Amount</th>
    </tr>';

    $sno = 1;
    foreach ($products as $product) {
        $html .= '<tr>';
        $html .= '<td>' . $sno++ . '</td>';
        $html .= '<td>' . htmlspecialchars($product['item_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['item_code'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['qty'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['unit'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['rate'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['amount'] ?? '') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    // Output PDF
    $mpdf->WriteHTML($html);
    $filename = 'Quotation_' . ($quotation['quotation_number'] ?? $quotationId) . '.pdf';
    $mpdf->Output($filename, 'D'); // Force download

} catch (Exception $e) {
    error_log("PDF Export Error: " . $e->getMessage());
    exit('Error generating PDF: ' . $e->getMessage());
}
?>
<?php
// PHP configuration to prevent output corruption
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Ensure all previous output is cleared before PDF generation starts.
if (ob_get_length()) {
    ob_end_clean();
}

// Composer autoload for mPDF and configuration files
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

// Ensure BASE_URL is defined if needed for images
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
    define('BASE_URL', str_replace('/superadmin/accounts/so/', '/', $base_url));
}

global $conn;

// Validate and sanitize the input
if (!isset($_GET['so_id']) || empty($_GET['so_id'])) {
    die('SO ID is required');
}

$so_id = intval($_GET['so_id']);

try {
    // Check for a valid database connection
    if (!$conn) {
        throw new Exception('Database connection not initialized.');
    }

    // Fetch main SO data from the database
    $stmt = $conn->prepare("SELECT * FROM po_main WHERE id = :so_id AND status IN ('Approved', 'Locked')");
    $stmt->execute(['so_id' => $so_id]);
    $so_main = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$so_main) {
        exit('SO not found or not approved');
    }

    // Fetch SO items
    $stmt2 = $conn->prepare("SELECT * FROM po_items WHERE po_id = :so_id ORDER BY id ASC");
    $stmt2->execute(['so_id' => $so_id]);
    $so_items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Initialize mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'tempDir' => __DIR__ . '/../../tmp',
        'default_font' => 'Helvetica'
    ]);

    // Your HTML content for the PDF
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.2; }
            .header { text-align: center; margin-bottom: 20px; }
            .header h1 { font-size: 24px; font-weight: bold; margin: 0; }
            .header h2 { font-size: 18px; font-weight: bold; margin-top: 5px; }
            .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
            .info-table td { border: 1px solid #000; padding: 5px; vertical-align: top; }
            .info-table .label { font-weight: bold; }
            .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .items-table th, .items-table td { border: 1px solid #000; padding: 5px; text-align: center; }
            .items-table th { background-color: #f2f2f2; font-weight: bold; }
            .items-table .text-left { text-align: left; }
            .summary-table { width: 40%; margin-left: auto; border-collapse: collapse; }
            .summary-table td { border: 1px solid #000; padding: 5px; font-weight: bold; }
            .footer { margin-top: 20px; }
            .footer-note { text-align: right; font-style: italic; font-size: 8px; }
            .signature { text-align: right; margin-top: 40px; }
            .signature-line { margin-top: 10px; }
        </style>
    </head>
    <body>
    ';

    // Header section
    $html .= '<div class="header">
        <h1>SELL ORDER</h1>
        <h2>SO Number: ' . htmlspecialchars($so_main['sell_order_number'] ?? $so_main['po_number']) . '</h2>
    </div>';

    // Information Table (replacing FPDF's Rect and Cells)
    $html .= '<table class="info-table">
        <tr>
            <td style="width:50%;">
                <div class="label">Invoice To</div>
                PUREWOOD<br>
                G-178, Boranada Industrial Park<br>
                BORANADA Jodhpur 342012<br>
                Rajasthan<br>
                GSTIN/UIN : AAQFP4054K1ZQ<br>
                State Name : RAJASTHAN, Code : 08<br>
                E-Mail : info@purewood.in
            </td>
            <td style="width:50%;">
                <table>
                    <tr>
                        <td class="label" style="width: 50%;">Voucher No.</td>
                        <td>' . htmlspecialchars($so_main['sell_order_number'] ?? $so_main['po_number']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Dated</td>
                        <td>' . date('d-M-y', strtotime($so_main['created_at'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Mode/Terms of Payment</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="label">Reference No. & Date</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="label">Other References</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>';

    // Consignee and Dispatch section
    $html .= '<table class="info-table">
        <tr>
            <td style="width:50%;">
                <div class="label">Consignee (Ship to)</div>
            </td>
            <td style="width:50%;">
                <table>
                    <tr>
                        <td class="label" style="width: 50%;">Dispatched through:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="label">Destination:</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>';

    // Supplier section
    $html .= '<table class="info-table">
        <tr>
            <td>
                <div class="label">Customer (Bill to)</div>
            </td>
        </tr>
    </table>';

    // Items table
    $html .= '<table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">Sl No.</th>
                <th style="width: 35%;">Description of Goods</th>
                <th style="width: 10%;">Due on</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 10%;">Rate</th>
                <th style="width: 5%;">per</th>
                <th style="width: 25%;">Amount</th>
            </tr>
        </thead>
        <tbody>';

    $totalAmount = 0;
    foreach ($so_items as $i => $item) {
        $html .= '<tr>
            <td>' . ($i + 1) . '</td>
            <td class="text-left">' . htmlspecialchars($item['product_name']) . '</td>
            <td></td>
            <td>' . htmlspecialchars($item['quantity']) . '</td>
            <td>' . number_format($item['price'], 2) . '</td>
            <td></td>
            <td>' . number_format($item['total_amount'], 2) . '</td>
        </tr>';
        $totalAmount += $item['total_amount'];
    }

    // Add empty rows to match FPDF's 8-row layout
    for ($j = count($so_items); $j < 8; $j++) {
        $html .= '<tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>';
    }

    $html .= '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right; font-weight: bold;">Total</td>
                <td style="font-weight: bold;">' . number_format($totalAmount, 2) . '</td>
            </tr>
        </tfoot>
    </table>';

    // Footer section
    $html .= '<div class="footer-note">E. & O.E</div>';

    $html .= '<div class="footer">
        <div style="float: left;">Company\'s PAN: AAQFP4054K</div>
        <div style="float: right;">For PUREWOOD</div>
        <div style="clear: both;"></div>
        <div class="signature">Authorised Signatory</div>
    </div>';

    $html .= '</body></html>';

    // Write HTML to mPDF and output the PDF
    $mpdf->WriteHTML($html);

    // Ensure no output buffer is active before headers
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="SO_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $so_main['sell_order_number'] ?? $so_main['po_number']) . '.pdf"');
    
    $mpdf->Output();
    exit;

} catch (Exception $e) {
    // Log the error to a file for debugging
    file_put_contents(__DIR__ . '/generate_pdf_error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    
    // Clear output buffer and show a friendly error message
    if (ob_get_length()) {
        ob_end_clean();
    }
    exit('Error generating PDF file. Please check the error log for details.');
}
?>
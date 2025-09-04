<?php
// PHP configuration to prevent output corruption
// Turn off error display to the browser, which can corrupt the PDF file.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Ensure all previous output is cleared before PDF generation starts.
if (ob_get_length()) {
    ob_end_clean();
}

// Composer autoload and configuration files
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
    define('BASE_URL', str_replace('/modules/quotation/', '/', $base_url));
}

global $conn;

// Validate and sanitize the input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid quotation ID');
}

$quotationId = intval($_GET['id']);

try {
    // Check for a valid database connection
    if (!$conn) {
        throw new Exception('Database connection not initialized.');
    }

    // Fetch quotation details from the database
    $stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ?");
    $stmt->execute([$quotationId]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$quotation) {
        exit('Quotation not found');
    }

    // Fetch product details for the quotation
    $stmt2 = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = ?");
    $stmt2->execute([$quotationId]);
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Initialize mPDF with enhanced settings for better compatibility
    $mpdf = new \Mpdf\Mpdf([
        'orientation' => 'L',
        'tempDir' => __DIR__ . '/../../tmp',
        'allow_remote_files' => true,
        'allow_output_buffering' => true,
        'debug' => false,
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    // Your HTML content for the PDF
    $html = '<html><head><style>
        body { font-family: Arial, sans-serif; font-size: 10px; line-height: 1.2; margin: 0; padding: 0; }
        .header-title { text-align: center; font-size: 24px; font-weight: bold; color: #8B4513; margin: 10px 0; font-family: "Times New Roman", serif; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { vertical-align: top; padding: 5px; border: 1px solid #000; }
        .header-table th { background-color: #f0f0f0; font-weight: bold; text-align: left; padding: 5px; border: 1px solid #000; }
        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px; }
        .product-table th, .product-table td { border: 1px solid #000; padding: 3px; vertical-align: middle; text-align: center; }
        .product-table th { background-color: #000000; color: #FFFFFF; font-weight: bold; }
        .product-table td.text-left { text-align: left; }
        .product-img { width: 50px; height: 50px; object-fit: contain; border: 1px solid #ccc; }
        .dimension-header-group { border-collapse: collapse; width: 100%; display: inline-table; }
        .dimension-header-group td { border: 0 !important; padding: 1px; font-size: 8px; font-weight: bold; }
        .total-summary-table { width: 30%; margin-left: auto; border-collapse: collapse; font-size: 10px; font-weight: bold; text-align: right; margin-bottom: 15px; }
        .total-summary-table td { border: 1px solid #000; padding: 5px; }
        .footer-section { background-color: #FFFF00; padding: 10px; border: 2px solid #000; font-size: 10px; line-height: 1.3; }
        .footer-title { font-weight: bold; font-size: 12px; margin-bottom: 5px; }
    </style></head><body>';

    // Header with PUREWOOD title
    $html .= '<div class="header-title">PUREWOOD</div>';

    // Header section with seller, buyer, and quotation details
    $html .= '<table class="header-table">
        <tr>
            <th style="width: 33%;">Seller Details</th>
            <th style="width: 33%;">Buyer Details</th>
            <th style="width: 34%;">Quotation Details</th>
        </tr>
        <tr>
            <td>
                <strong>Purewood</strong><br>
                G-178 Special Economy Zone (Sez)<br>
                Export Promotion Industrial Park (EPIP)<br>
                Boranada, Jodhpur, Rajasthan, India<br>
                Postal Code: 342001
            </td>
            <td>
                <strong>' . htmlspecialchars($quotation['customer_name'] ?? 'N/A') . '</strong><br>
                ' . htmlspecialchars($quotation['customer_address'] ?? 'N/A') . '<br>
                ' . htmlspecialchars($quotation['customer_city'] ?? 'N/A') . '<br>
                ' . htmlspecialchars($quotation['customer_state'] ?? 'N/A') . '
            </td>
            <td>
                <strong>Delivery Term:</strong> ' . htmlspecialchars($quotation['delivery_term'] ?? '90 Days') . '<br>
                <strong>Terms of Delivery:</strong> ' . htmlspecialchars($quotation['terms_of_delivery'] ?? 'Ex-factory') . '<br>
                <strong>Payment Term:</strong> ' . htmlspecialchars($quotation['payment_term'] ?? '30% advance 70% on DOCS') . '<br>
                <strong>Inspection:</strong> Internal<br>
                <strong>Quotation Number:</strong> ' . htmlspecialchars($quotation['quotation_number'] ?? 'N/A') . '<br>
                <strong>Date of PI Raised:</strong> ' . htmlspecialchars($quotation['quotation_date'] ?? 'N/A') . '<br>
            </td>
        </tr>
    </table>';

    // Product table with detailed columns
    $html .= '<table class="product-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">Sno</th>
                <th rowspan="2" style="width: 7%;">Image</th>
                <th rowspan="2" style="width: 12%;">Item Name</th>
                <th rowspan="2" style="width: 7%;">Description</th>
                <th rowspan="2" style="width: 5%;">Assembly</th>
                <th colspan="2" style="width: 24%;">Dimensions in CMS</th>
                <th rowspan="2" style="width: 5%;">CBM</th>
                <th rowspan="2" style="width: 6%;">TOTAL CBM</th>
                <th rowspan="2" style="width: 10%;">MATERIAL</th>
                <th rowspan="2" style="width: 5%;">No of Packet</th>
                <th rowspan="2" style="width: 5%;">Finish</th>
                <th rowspan="2" style="width: 5%;">Qty.</th>
                <th rowspan="2" style="width: 5%;">Price USD</th>
                <th rowspan="2" style="width: 7%;">Total USD</th>
                <th rowspan="2" style="width: 7%;">Comments</th>
            </tr>
            <tr>
                <th>Item (H x W x D)</th>
                <th>Box (W x D x H)</th>
            </tr>
        </thead>
        <tbody>';

    $serial = 1;
    $totalQty = 0;
    $totalUsd = 0;
    $totalCbm = 0;

    foreach ($products as $product) {
        $html .= '<tr>';
        $html .= '<td>' . $serial . '</td>';

        // Simplified and robust image handling for mPDF
        $imageHtml = '';
        $productImageName = $product['product_image_name'] ?? '';
        
        if (!empty($productImageName)) {
            $imageFound = false;
            
            // First, try to find the image in uploads/quotation directory
            $uploadPath = __DIR__ . '/../../uploads/quotation/' . $productImageName;
            
            if (file_exists($uploadPath)) {
                // Use file:// protocol for local files
                $absolutePath = realpath($uploadPath);
                if ($absolutePath) {
                    // Convert Windows backslashes to forward slashes
                    $absolutePath = str_replace('\\', '/', $absolutePath);
                    $imageHtml = '<img src="file://' . $absolutePath . '" class="product-img" style="max-width: 50px; max-height: 50px;" />';
                    $imageFound = true;
                }
            }
            
            if (!$imageFound) {
                // Try alternative path
                $altPath = __DIR__ . '/../../assets/images/upload/quotation/' . $productImageName;
                if (file_exists($altPath)) {
                    $absolutePath = realpath($altPath);
                    if ($absolutePath) {
                        $absolutePath = str_replace('\\', '/', $absolutePath);
                        $imageHtml = '<img src="file://' . $absolutePath . '" class="product-img" style="max-width: 50px; max-height: 50px;" />';
                        $imageFound = true;
                    }
                }
            }
            
            if (!$imageFound) {
                // Fallback to web URL
                $webUrl = BASE_URL . 'uploads/quotation/' . rawurlencode($productImageName);
                $imageHtml = '<img src="' . htmlspecialchars($webUrl) . '" class="product-img" style="max-width: 50px; max-height: 50px;" />';
            }
        } else {
            // Placeholder if no image name is provided
            $imageHtml = '<div style="width: 50px; height: 50px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #666;">No Image</div>';
        }

        $html .= '<td style="width: 70px; height: 70px; text-align: center; vertical-align: middle;">' . $imageHtml . '</td>';

        // Product details
        $html .= '<td class="text-left">' . htmlspecialchars($product['item_name'] ?? 'N/A') . '</td>';
        $html .= '<td class="text-left">' . htmlspecialchars($product['description'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['assembly'] ?? 'KD') . '</td>';
        $itemH = htmlspecialchars($product['item_h'] ?? 'N/A');
        $itemW = htmlspecialchars($product['item_w'] ?? 'N/A');
        $itemD = htmlspecialchars($product['item_d'] ?? 'N/A');
        $html .= '<td>' . $itemH . 'cm (' . round($itemH * 0.393701, 1) . '") x ' . $itemW . 'cm (' . round($itemW * 0.393701, 1) . '") x ' . $itemD . 'cm (' . round($itemD * 0.393701, 1) . '")</td>';
        $boxW = htmlspecialchars($product['box_w'] ?? 'N/A');
        $boxD = htmlspecialchars($product['box_d'] ?? 'N/A');
        $boxH = htmlspecialchars($product['box_h'] ?? 'N/A');
        $html .= '<td>' . $boxW . ' x ' . $boxD . ' x ' . $boxH . '</td>';
        $cbm = floatval($product['cbm'] ?? 0);
        $qty = intval($product['quantity'] ?? 0);
        $totalCbmRow = $cbm * $qty;
        $totalUsdRow = floatval($product['price_usd'] ?? 0) * $qty;
        $html .= '<td>' . number_format($cbm, 2) . '</td>';
        $html .= '<td>' . number_format($totalCbmRow, 2) . '</td>';
        $html .= '<td class="text-left">' . htmlspecialchars($product['wood_type'] ?? 'N/A') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['no_of_packet'] ?? 'N/A') . '</td>';
        $html .= '<td>' . htmlspecialchars($product['finish'] ?? 'N/A') . '</td>';
        $html .= '<td>' . $qty . '</td>';
        $html .= '<td>$ ' . number_format(floatval($product['price_usd'] ?? 0), 2) . '</td>';
        $html .= '<td>$ ' . number_format($totalUsdRow, 2) . '</td>';
        $html .= '<td class="text-left">' . htmlspecialchars($product['comments'] ?? '') . '</td>';
        $html .= '</tr>';
        $totalQty += $qty;
        $totalUsd += $totalUsdRow;
        $totalCbm += $totalCbmRow;
        $serial++;
    }

    $html .= '</tbody></table>';

    // Total summary table
    $html .= '<table class="total-summary-table">
        <tr>
            <td>Total CBM:</td>
            <td>' . number_format($totalCbm, 1) . '</td>
        </tr>
        <tr>
            <td>Total Qty:</td>
            <td>' . $totalQty . '</td>
        </tr>
        <tr>
            <td>Total USD:</td>
            <td>$ ' . number_format($totalUsd, 2) . '</td>
        </tr>
    </table>';

    // Bank details footer
    $html .= '<div class="footer-section">
        <div class="footer-title">Bank Details</div>
        <div>
            <strong>Beneficiary details:</strong> Purewood<br>
            <strong>Beneficiary Address:</strong> G-178 Special Economy Zone (Sez)<br>
            <strong>Beneficiary Bank Name:</strong> HDFC Bank Account No: 50200007209183<br>
            <strong>Beneficiary Bank Address:</strong> HDFC BANK, BORANADANODHPUR<br>
            <strong>Swift Code:</strong> HDFCINBB
        </div>
    </div>';

    $html .= '</body></html>';

    // Write HTML to mPDF and output the PDF
    $mpdf->WriteHTML($html);

    // Ensure no output buffer is active before headers
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="quotation_' . ($quotation['quotation_number'] ?? $quotationId) . '.pdf"');
    
    $mpdf->Output();
    exit;

} catch (Exception $e) {
    // Log the error to a file for debugging
    file_put_contents(__DIR__ . '/export_quotation_pdf_error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    
    // Clear output buffer and show a friendly error message
    if (ob_get_length()) {
        ob_end_clean();
    }
    exit('Error generating PDF file. Please check the error log for details.');
}
?>
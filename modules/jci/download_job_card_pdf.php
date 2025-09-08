<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (ob_get_length()) ob_end_clean();

require __DIR__ . '/../../vendor/autoload.php';

use \Mpdf\Mpdf;

include_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['jci_number']) || empty($_GET['jci_number'])) {
    die('JCI Number is required');
}

$jci_number = $_GET['jci_number'];

try {
    global $conn;

    if (!$conn) {
        exit('Database connection not established.');
    }

    // Font file configuration
    $fontDirPath = __DIR__;
    $fontFile = 'NotoSansDevanagari.ttf';
    $fontFullPath = $fontDirPath . '/' . $fontFile;

    if (!file_exists($fontFullPath)) {
        die("Font file not found at: " . $fontFullPath);
    }

    // Fetch job card data
    $sql = "SELECT j.id, j.jci_number, j.jci_date, j.created_by, j.po_id, j.bom_id,
                   p.po_number, p.client_name,
                   b.bom_number
            FROM jci_main j
            LEFT JOIN po_main p ON j.po_id = p.id
            LEFT JOIN bom_main b ON j.bom_id = b.id
            WHERE j.jci_number = :jci_number
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':jci_number', $jci_number);
    $stmt->execute();
    $job_card = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job_card) {
        exit('Job Card not found');
    }

    $jci_id = $job_card['id'];
    $po_id = $job_card['po_id'];

    // Fetch SO Number
    $sql_so = "SELECT sell_order_number FROM sell_order WHERE po_id = :po_id LIMIT 1";
    $stmt_so = $conn->prepare($sql_so);
    $stmt_so->bindParam(':po_id', $po_id);
    $stmt_so->execute();
    $so = $stmt_so->fetch(PDO::FETCH_ASSOC);
    $so_number = $so['sell_order_number'] ?? '';

    // Fetch items
    $sql_items = "SELECT product_name, item_code, quantity as assign_quantity, labour_cost, delivery_date, total_amount, contracture_name, job_card_number
                  FROM jci_items
                  WHERE jci_id = :jci_id";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bindParam(':jci_id', $jci_id);
    $stmt_items->execute();
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    // Hindi note - with non-breaking spaces (\u{00A0}) in "जारी किया जाएगा"
    $hindi_note = 'नोट: यदि किसी कार्य (वितरण) को निर्धारित जॉब कार्ड की डिलीवरी तिथि तक पूर्ण नहीं किया गया, तो डेबिट नोट जारी' . "\u{00A0}" . 'किया' . "\u{00A0}" . 'जाएगा।';

    // Build HTML content
    $html = '<h2 style="text-align:center;">' . htmlspecialchars($items[0]['job_card_number'] ?? 'JOB CARD') . '</h2>';

    $html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%" style="border-collapse: collapse; font-size: 10px;">';
    $html .= '<tr><td colspan="2"><strong>COMPANY</strong></td><td colspan="4">' . htmlspecialchars($items[0]['contracture_name'] ?? '') . '</td></tr>';
    $html .= '<tr><td colspan="2"><strong>PAN CARD:</strong></td><td colspan="4"></td></tr>';
    $html .= '<tr><td colspan="2"><strong>AADHAR NO:</strong></td><td colspan="4"></td></tr>';
    $html .= '<tr><td colspan="2"><strong>ADDRESS:</strong></td><td colspan="4"></td></tr>';

    $html .= '<tr><td colspan="6" style="font-size: 8px; font-style: italic; padding: 5px; font-family: notosansdevanagari;">
                ' . htmlspecialchars($hindi_note) . '
              </td></tr>';

    $html .= '<tr>
                <td><strong>JCN</strong></td>
                <td>' . htmlspecialchars($job_card['jci_number'] ?? '') . '</td>
                <td><strong>Date</strong></td>
                <td>' . htmlspecialchars($job_card['jci_date'] ?? '') . '</td>
                <td><strong>SO NO</strong></td>
                <td>' . htmlspecialchars($so_number) . '</td>
              </tr>';

    $html .= '<tr>
                <td><strong>BUYER PO</strong></td>
                <td>' . htmlspecialchars($job_card['client_name'] ?? '') . '</td>
                <td><strong>BOM NO</strong></td>
                <td>' . htmlspecialchars($job_card['bom_number'] ?? '') . '</td>
                <td><strong>DELIVERY DATE</strong></td>
                <td>' . htmlspecialchars($items[0]['delivery_date'] ?? '') . '</td>
              </tr>';

    $html .= '<tr>
                <td><strong>ITEM NAME</strong></td>
                <td>' . htmlspecialchars($items[0]['product_name'] ?? '') . '</td>
                <td><strong>ITEM CODE</strong></td>
                <td>' . htmlspecialchars($items[0]['item_code'] ?? '') . '</td>
                <td><strong>ITEM QTY</strong></td>
                <td>' . htmlspecialchars($items[0]['assign_quantity'] ?? '') . '</td>
              </tr>';

    $html .= '<tr>
                <td><strong>LABOUR RATE</strong></td>
                <td>' . htmlspecialchars($items[0]['labour_cost'] ?? '') . '</td>
                <td colspan="4"></td>
              </tr>';

    for ($i = 0; $i < 15; $i++) {
        $html .= '<tr>';
        for ($j = 0; $j < 6; $j++) {
            $html .= '<td style="border: 1px solid black;">&nbsp;</td>';
        }
        $html .= '</tr>';
    }

    $html .= '<tr>
                <td colspan="5" style="text-align:right;"><strong>TOTAL AMOUNT</strong></td>
                <td>' . htmlspecialchars(array_sum(array_column($items, 'total_amount'))) . '</td>
              </tr>';

    $html .= '</table>';

    // mPDF config with font
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf([
        'fontDir' => array_merge($fontDirs, [$fontDirPath]),
        'fontdata' => $fontData + [
            'notosansdevanagari' => [
                'R' => $fontFile,
            ]
        ],
        'default_font' => 'notosansdevanagari'
    ]);

    $mpdf->WriteHTML($html);
    $filename = 'JobCard_' . $jci_number . '.pdf';
    $mpdf->Output($filename, 'D');

} catch (Exception $e) {
    error_log("Job Card PDF Export Error: " . $e->getMessage());
    exit('Error generating PDF: ' . $e->getMessage());
}

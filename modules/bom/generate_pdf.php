<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (ob_get_length()) {
    ob_end_clean();
}

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['bom_id']) || empty($_GET['bom_id'])) {
    die('BOM ID is required');
}

$bom_id = intval($_GET['bom_id']);
global $conn;

try {
    // Fetch BOM main data
    $stmt = $conn->prepare("SELECT * FROM bom_main WHERE id = :bom_id");
    $stmt->execute(['bom_id' => $bom_id]);
    $bom_main = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bom_main) {
        die('BOM not found');
    }

    // Fetch related BOM items from various tables
    function fetchItems($conn, $table, $bom_id) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE bom_main_id = :bom_id");
        $stmt->execute(['bom_id' => $bom_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $wood_items = fetchItems($conn, 'bom_wood', $bom_id);
    $glue_items = fetchItems($conn, 'bom_glow', $bom_id);
    $plynydf_items = fetchItems($conn, 'bom_plynydf', $bom_id);
    $hardware_items = fetchItems($conn, 'bom_hardware', $bom_id);
    $labour_items = fetchItems($conn, 'bom_labour', $bom_id);
    $factory_items = fetchItems($conn, 'bom_factory', $bom_id);
    $margin_items = fetchItems($conn, 'bom_margin', $bom_id);

    // Initialize mPDF
    $mpdf = new \Mpdf\Mpdf([
        'orientation' => 'P',
        'tempDir' => __DIR__ . '/../../tmp',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    // HTML content for PDF
    $html = '<html><head><style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .section-title { background-color: #f0f0f0; padding: 5px; text-align: center; font-weight: bold; border: 1px solid #000; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; font-size: 9px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .summary-table { width: 50%; margin-left: auto; }
    </style></head><body>';

    $html .= '<div class="header">BILL OF MATERIAL REPORT - ' . htmlspecialchars($bom_main['bom_number']) . '</div>';
    $html .= '<div class="section-title">BUYER\'S DETAILS</div>';
    $html .= '<table>';
    $html .= '<tr>';
    $html .= '<th>NAME</th><th>BUYER PO</th><th>ITEM NAME</th><th>SALE ORDER</th><th>ITEM CODE</th><th>QTY</th><th>RATE</th><th>AMOUNT</th>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($bom_main['client_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['buyer_po'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['item_name'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['sale_order'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['item_code'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['qty'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['rate'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($bom_main['amount'] ?? '') . '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '<div class="section-title">BILL OF MATERIAL DETAIL OF ABOVE ITEM MANUFACTURING</div>';
    
    if (!empty($wood_items)) {
        $html .= '<h3>Wood Items</h3>';
        $html .= '<table>';
        $html .= '<tr><th>WOOD</th><th>PART</th><th>LENGTH</th><th>THICKNESS</th><th>WIDTH</th><th>QTY</th><th>CFT</th><th>RATE</th><th>AMOUNT</th></tr>';
        foreach ($wood_items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['woodtype'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['part'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['length_ft'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['thickness_inch'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['width_ft'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['quantity'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['cft'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['price'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['total'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    $html .= '<h3>Cost Summary</h3>';
    $html .= '<table class="summary-table">';
    $html .= '<tr><th>DESCRIPTION</th><th>AMOUNT</th></tr>';
    
    $summary = [
        'WOOD' => array_sum(array_column($wood_items, 'total')),
        'HARDWARE' => array_sum(array_column($hardware_items, 'totalprice')),
        'LABOUR' => array_sum(array_column($labour_items, 'totalprice')),
        'LABOUR EXTRA' => $bom_main['labour_extra'] ?? 0,
        'CNC' => $bom_main['cnc'] ?? 0,
        'CHERAI' => $bom_main['cherai'] ?? 0,
        'MDF/PLY' => array_sum(array_column($plynydf_items, 'total')),
        'KHERAT' => $bom_main['kherat'] ?? 0,
        'OTHER' => $bom_main['other'] ?? 0,
        'GROSS AMT' => $bom_main['gross_amount'] ?? 0,
        'FACTORY COST' => array_sum(array_column($factory_items, 'factory_cost')),
        'PROFIT 20%' => $bom_main['profit_20'] ?? 0,
        'DISCOUNT' => $bom_main['discount'] ?? 0,
        'MARGIN' => array_sum(array_column($margin_items, 'margin_cost')),
        'Total' => $bom_main['total_amount'] ?? 0,
    ];
    
    foreach ($summary as $desc => $amt) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($desc) . '</td>';
        $html .= '<td>' . number_format($amt, 2) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $html .= '</body></html>';
    
    // Write HTML to mPDF and output
    $mpdf->WriteHTML($html);
    
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    $filename = htmlspecialchars($bom_main['bom_number']) . '.pdf';
    
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
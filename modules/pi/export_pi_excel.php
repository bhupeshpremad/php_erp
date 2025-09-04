<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

include_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['id'])) {
    die('PI ID is required');
}

$piId = intval($_GET['id']);

try {
    // $database = new Database();
    // $conn = $database->getConnection();

    global $conn;


    // Fetch PI data
    $stmt = $conn->prepare("SELECT * FROM pi WHERE pi_id = ?");
    $stmt->execute([$piId]);
    $pi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pi) {
        die('PI not found');
    }

    // Fetch products for this PI's quotation
    $stmt2 = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = (SELECT id FROM quotations WHERE quotation_number = ?)");
    $stmt2->execute([$pi['quotation_number']]);
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Purewood')
        ->setTitle('Proforma Invoice Export')
        ->setSubject('Proforma Invoice Export')
        ->setDescription('Exported Proforma Invoice in Excel format');

    // Set main title
    $sheet->setCellValue('A1', 'PUREWOOD');
    $sheet->mergeCells('A1:T1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Seller Details block
    $sheet->setCellValue('A2', 'Seller Details');
    $sheet->mergeCells('A2:D2');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->setCellValue('A3', 'Purewood');
    $sheet->setCellValue('A4', 'G178 Special Economy Area (SEZ)');
    $sheet->setCellValue('A5', 'Export Promotion Industrial Park (EPIP)');
    $sheet->setCellValue('A6', 'Boranada, Jodhpur, Rajasthan');
    $sheet->setCellValue('A7', 'India (342001) GST: 08AAQFP4054K1ZQ');
    $sheet->getStyle('A3:A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    $sheet->getStyle('A3:A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Buyer Details block
    $sheet->setCellValue('E2', 'Buyer Details');
    $sheet->mergeCells('E2:G2');
    $sheet->getStyle('E2')->getFont()->setBold(true);
    $sheet->getStyle('E2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    // Fetch buyer details from quotations table
    $stmtBuyer = $conn->prepare("SELECT customer_name, customer_email, customer_phone FROM quotations WHERE quotation_number = ?");
    $stmtBuyer->execute([$pi['quotation_number']]);
    $buyer = $stmtBuyer->fetch(PDO::FETCH_ASSOC);
    $sheet->setCellValue('E3', $buyer['customer_name'] ?? '');
    $sheet->setCellValue('E4', $buyer['customer_email'] ?? '');
    $sheet->setCellValue('E5', $buyer['customer_phone'] ?? '');
    $sheet->getStyle('E3:E5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    $sheet->getStyle('E3:E5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Payment Terms block
    $sheet->setCellValue('J2', 'Payment Terms');
    $sheet->setCellValue('K2', $pi['delivery_term'] ?? '60 Days');
    $sheet->mergeCells('J2:L2');
    $sheet->getStyle('J2:L2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Terms of Delivery block
    $sheet->setCellValue('J3', 'Terms of Delivery');
    $sheet->setCellValue('K3', $pi['terms_of_delivery'] ?? 'FOB');
    $sheet->mergeCells('J3:L3');
    $sheet->getStyle('J3:L3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Payment Term block
    $sheet->setCellValue('J4', 'Payment Term');
    $sheet->setCellValue('K4', $pi['payment_term'] ?? '30% advance 70% on DOCS');
    $sheet->mergeCells('J4:L4');
    $sheet->getStyle('J4:L4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // PI Number block
    $sheet->setCellValue('O2', 'PI Number');
    $sheet->setCellValue('P2', $pi['pi_number'] ?? '');
    $sheet->mergeCells('O2:T2');
    $sheet->getStyle('O2:T2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Date of PI Raised block
    $sheet->setCellValue('O3', 'Date of PI Raised');
    $sheet->setCellValue('P3', $pi['date_of_pi_raised'] ?? '');
    $sheet->mergeCells('O3:T3');
    $sheet->getStyle('O3:T3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Style header row (row 6)
    $headerRow = 6;
    $sheet->getStyle("A$headerRow:T$headerRow")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle("A$headerRow:T$headerRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4B612C');
    $sheet->getStyle("A$headerRow:T$headerRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$headerRow:T$headerRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    // Merge cells for grouped headers
    $sheet->mergeCells("F$headerRow:H$headerRow"); // Item Dimension
    $sheet->mergeCells("I$headerRow:K$headerRow"); // Box Dimension

    // Set header titles with merged cells
    $sheet->setCellValue("A$headerRow", 'Sno');
    $sheet->setCellValue("B$headerRow", 'Image');
    $sheet->setCellValue("C$headerRow", 'Item Name/ Code');
    $sheet->setCellValue("D$headerRow", 'Description');
    $sheet->setCellValue("E$headerRow", 'Assembly');
    $sheet->setCellValue("F$headerRow", 'Item Dimension');
    $sheet->setCellValue("I$headerRow", 'Box Dimension');
    $sheet->setCellValue("L$headerRow", 'CBM');
    $sheet->setCellValue("M$headerRow", 'Wood/ Marble Type');
    $sheet->setCellValue("N$headerRow", 'No of Packet');
    $sheet->setCellValue("O$headerRow", 'Iron Gauge');
    $sheet->setCellValue("P$headerRow", 'MDF Finish');
    $sheet->setCellValue("Q$headerRow", 'MOQ');
    $sheet->setCellValue("R$headerRow", 'Price USD');
    $sheet->setCellValue("S$headerRow", 'Total');
    $sheet->setCellValue("T$headerRow", 'Comments');

    // Add sub-headers for Item Dimension and Box Dimension in row 7
    $subHeaderRow = 7;
    $sheet->setCellValue("F$subHeaderRow", 'H');
    $sheet->setCellValue("G$subHeaderRow", 'W');
    $sheet->setCellValue("H$subHeaderRow", 'D');
    $sheet->setCellValue("I$subHeaderRow", 'H');
    $sheet->setCellValue("J$subHeaderRow", 'W');
    $sheet->setCellValue("K$subHeaderRow", 'D');

    // Style sub-header row
    $sheet->getStyle("A$subHeaderRow:T$subHeaderRow")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle("A$subHeaderRow:T$subHeaderRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4B612C');
    $sheet->getStyle("A$subHeaderRow:T$subHeaderRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$subHeaderRow:T$subHeaderRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

     // Fill product rows starting from row 8
$row = 8;
$serial = 1;
$totalAmount = 0;
foreach ($products as $product) {
    $sheet->setCellValue("A$row", $serial);

    // Insert image if available and file exists
    if (!empty($product['product_image_name'])) {
        // Construct full image path based on known base directory and image file name
        $baseImagePath = realpath(__DIR__ . '/../../assets/images/upload/quotation/');
        $imageFileName = $product['product_image_name'];
        $imagePath = $baseImagePath . DIRECTORY_SEPARATOR . $imageFileName;

        // Log the image path being checked
        error_log("Checking image path: " . $imagePath);

        // Check if image file exists before inserting
        if (file_exists($imagePath)) {
            error_log("Inserting image: " . $imagePath);

            $drawing = new Drawing();
            $drawing->setName('Product Image');
            $drawing->setDescription('Product Image');
            $drawing->setPath($imagePath);
            $drawing->setHeight(50);
            $drawing->setWidth(40);
            $drawing->setCoordinates("B$row");
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        } else {
            error_log("Image file not found: " . $imagePath);

            // Test with a hardcoded image path to verify insertion works
            $testImagePath = realpath(__DIR__ . '/../../assets/images/upload/quotation/test_image.jpg');
            if ($testImagePath && file_exists($testImagePath)) {
                error_log("Inserting test image: " . $testImagePath);
                $drawing = new Drawing();
                $drawing->setName('Test Image');
                $drawing->setDescription('Test Image');
                $drawing->setPath($testImagePath);
                $drawing->setHeight(60);
                $drawing->setCoordinates("B$row");
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
            } else {
                error_log("Test image not found: " . $testImagePath);
            }
        }
    }

    $sheet->setCellValue("C$row", $product['item_name'] . "\n" . $product['item_code']);
    $sheet->setCellValue("D$row", $product['description']);
    $sheet->setCellValue("E$row", $product['assembly']);
    $sheet->setCellValue("F$row", $product['item_h']);
    $sheet->setCellValue("G$row", $product['item_w']);
    $sheet->setCellValue("H$row", $product['item_d']);
    $sheet->setCellValue("I$row", $product['box_h']);
    $sheet->setCellValue("J$row", $product['box_w']);
    $sheet->setCellValue("K$row", $product['box_d']);
    $sheet->setCellValue("L$row", $product['cbm']);
    $sheet->setCellValue("M$row", $product['wood_type']);
    $sheet->setCellValue("N$row", $product['no_of_packet']);
    $sheet->setCellValue("O$row", $product['iron_gauge']);
    $sheet->setCellValue("P$row", $product['mdf_finish']);
    $sheet->setCellValue("Q$row", $product['moq']);
    $sheet->setCellValue("R$row", '$' . $product['price_usd']);
    $sheet->setCellValue("S$row", '$' . $product['total']);
    $sheet->setCellValue("T$row", $product['comments']);
    $totalAmount += $product['total'];
    $row++;
    $serial++;
}

// Set column widths for better appearance
$columnWidths = [
    'A' => 6, 'B' => 15, 'C' => 20, 'D' => 30, 'E' => 10,
    'F' => 8, 'G' => 8, 'H' => 8, 'I' => 8, 'J' => 8,
    'K' => 8, 'L' => 10, 'M' => 15, 'N' => 10, 'O' => 10,
    'P' => 10, 'Q' => 8, 'R' => 12, 'S' => 12, 'T' => 20
];
foreach ($columnWidths as $col => $width) {
    $sheet->getColumnDimension($col)->setWidth($width);
}

// Apply background colors to specific columns (dark green for headers, gray for some columns)
$darkGreen = '4B612C';
$gray = 'D9D9D9';

// Header rows already styled with dark green

// Apply gray fill to columns I, J, K (Box Dimension)
$sheet->getStyle("I$subHeaderRow:K$subHeaderRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($gray);
for ($r = $row - count($products); $r < $row; $r++) {
    $sheet->getStyle("I$r:K$r")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($gray);
}

// Apply borders to all cells in the data range including headers and product rows
$lastRow = $row - 1;
$sheet->getStyle("A$headerRow:T$lastRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));

// Clean output buffer again before output
if (ob_get_length()) ob_end_clean();

// Output to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="pi_' . $pi['pi_number'] . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/export_pi_excel_error.log', $e->getMessage());
    exit('Error generating Excel file.');
}

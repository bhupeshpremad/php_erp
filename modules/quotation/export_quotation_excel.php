<?php
// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (ob_get_length()) ob_end_clean();

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

include_once __DIR__ . '/../../config/config.php';

global $conn;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid quotation ID');
}

$quotationId = intval($_GET['id']);

try {
    if (!$conn) {
        throw new Exception('Database connection not initialized.');
    }

    $stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ?");
    $stmt->execute([$quotationId]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$quotation) exit('Quotation not found');

    $stmt2 = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = ?");
    $stmt2->execute([$quotationId]);
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $spreadsheet->getProperties()
        ->setCreator('Purewood')
        ->setTitle('Quotation Export')
        ->setSubject('Quotation Export')
        ->setDescription('Exported Quotation in Excel format');

    $sheet->setCellValue('A1', 'PUREWOOD');
    $sheet->mergeCells('A1:T1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // --- START OF REVISED LAYOUT FOR HEADER BLOCKS ---

    // Seller Details - Occupying Rows 2 to 5
    $sheet->setCellValue('A2', 'Seller Details');
    $sheet->mergeCells('A2:D2');
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->setCellValue('A3', 'Purewood');
    $sheet->setCellValue('A4', 'G178 Special Economy Area (SEZ)');
    $sheet->setCellValue('A5', 'Export Promotion Industrial Park (EPIP)');
    // Place GST information immediately after EPIP in the same block
    $sheet->setCellValue('A6', 'Boranada, Jodhpur, Rajasthan, India (342001) GST: 08AAQFP4054K1ZQ'); // Combined into one line for A6
    
    // Adjust style for the seller address block
    $sheet->getStyle('A3:A6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    $sheet->getStyle('A3:A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


    // Buyer Details - Occupying Rows 2 to 5, horizontally aligned with Seller Details
    $sheet->setCellValue('E2', 'Buyer Details');
    $sheet->mergeCells('E2:H2');
    $sheet->getStyle('E2')->getFont()->setBold(true);
    $sheet->getStyle('E2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->setCellValue('E3', $quotation['customer_name'] ?? '');
    $sheet->setCellValue('E4', $quotation['customer_email'] ?? '');
    $sheet->setCellValue('E5', $quotation['customer_phone'] ?? '');
    $sheet->getStyle('E3:E5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    $sheet->getStyle('E3:E5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Payment/Delivery Terms - Occupying Rows 2 to 3, horizontally aligned
    $sheet->setCellValue('J2', 'Payment Terms: ' . ($quotation['delivery_term'] ?? '60 Days'));
    $sheet->mergeCells('J2:L2');
    $sheet->getStyle('J2:L2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    $sheet->setCellValue('J3', 'Terms of Delivery: ' . ($quotation['terms_of_delivery'] ?? 'FOB'));
    $sheet->mergeCells('J3:L3');
    $sheet->getStyle('J3:L3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Quote Number & Date - Occupying Rows 2 to 3, horizontally aligned
    $sheet->setCellValue('O2', 'Quote Number: ' . ($quotation['quotation_number'] ?? ''));
    $sheet->mergeCells('O2:T2');
    $sheet->getStyle('O2:T2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    $sheet->setCellValue('O3', 'Date of Quote Raised: ' . ($quotation['quotation_date'] ?? ''));
    $sheet->mergeCells('O3:T3');
    $sheet->getStyle('O3:T3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // --- END OF REVISED LAYOUT FOR HEADER BLOCKS ---

    // The product table header will now reliably start after these header blocks.
    // We can define the header starting row based on the last row used in the top header section.
    // In this case, seller details go up to A6, so the next available row for product headers is row 7.
    $headerRow = 7;
    $sheet->getStyle("A$headerRow:R$headerRow")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle("A$headerRow:R$headerRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4B612C');
    $sheet->getStyle("A$headerRow:R$headerRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$headerRow:R$headerRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    $sheet->mergeCells("C$headerRow:E$headerRow");
    $sheet->mergeCells("F$headerRow:H$headerRow");
    $sheet->mergeCells("I$headerRow:K$headerRow");

    $sheet->setCellValue("A$headerRow", 'Sno');
    $sheet->setCellValue("B$headerRow", 'Image');
    $sheet->setCellValue("C$headerRow", 'Item Name/ Code');
    $sheet->setCellValue("F$headerRow", 'Item Dimension');
    $sheet->setCellValue("I$headerRow", 'Box Dimension');
    $sheet->setCellValue("L$headerRow", 'CBM');
    $sheet->setCellValue("M$headerRow", 'Wood/ Marble Type');
    $sheet->setCellValue("N$headerRow", 'No of Packet');
    $sheet->setCellValue("O$headerRow", 'Quantity');
    $sheet->setCellValue("P$headerRow", 'Price USD');
    $sheet->setCellValue("Q$headerRow", 'Total');
    $sheet->setCellValue("R$headerRow", 'Comments');

    $subHeaderRow = 8; // Moved down to accommodate the combined A6 line
    $sheet->setCellValue("C$subHeaderRow", 'Item Name');
    $sheet->setCellValue("D$subHeaderRow", 'Item Code');
    $sheet->setCellValue("E$subHeaderRow", 'Assembly');
$sheet->setCellValue("F$subHeaderRow", 'W');
$sheet->setCellValue("G$subHeaderRow", 'D');
$sheet->setCellValue("H$subHeaderRow", 'H');
$sheet->setCellValue("I$subHeaderRow", 'W');
$sheet->setCellValue("J$subHeaderRow", 'D');
$sheet->setCellValue("K$subHeaderRow", 'H');

    $sheet->getStyle("A$subHeaderRow:R$subHeaderRow")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle("A$subHeaderRow:R$subHeaderRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4B612C');
    $sheet->getStyle("A$subHeaderRow:R$subHeaderRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A$subHeaderRow:R$subHeaderRow")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    $row = 9; // Product data now starts on row 9
    $serial = 1;
    foreach ($products as $product) {
        $sheet->setCellValue("A$row", $serial);
        $sheet->getRowDimension($row)->setRowHeight(75);

        $imageInserted = false;
        if (!empty($product['product_image_name'])) {
            $baseImagePath = ROOT_DIR_PATH . 'assets/images/upload/quotation/';
            $imageFileName = $product['product_image_name'];

            $imagePath = realpath($baseImagePath . $imageFileName);

            if ($imagePath && file_exists($imagePath)) {
                $drawing = new Drawing();
                $drawing->setName('Product Image');
                $drawing->setDescription('Product Image');
                $drawing->setPath($imagePath);
                $drawing->setHeight(70);
                $drawing->setWidth(70);
                $drawing->setCoordinates("B$row");
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
                $imageInserted = true;
            } else {
                error_log("Image not found for Excel export: " . $baseImagePath . $imageFileName);
            }
        }
        if (!$imageInserted) {
            $sheet->setCellValue("B$row", 'No Image');
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B$row")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        $sheet->setCellValue("C$row", $product['item_name']);
        $sheet->setCellValue("D$row", $product['item_code']);
        $sheet->setCellValue("E$row", $product['assembly']);
$sheet->setCellValue("F$row", $product['item_w']);
$sheet->setCellValue("G$row", $product['item_d']);
$sheet->setCellValue("H$row", $product['item_h']);
$sheet->setCellValue("I$row", $product['box_w']);
$sheet->setCellValue("J$row", $product['box_d']);
$sheet->setCellValue("K$row", $product['box_h']);
        $sheet->setCellValue("L$row", $product['cbm']);
        $sheet->setCellValue("M$row", $product['wood_type']);
        $sheet->setCellValue("N$row", $product['no_of_packet']);
        $sheet->setCellValue("O$row", $product['quantity']);
        $sheet->setCellValue("P$row", $product['price_usd']);
        $total = is_numeric($product['quantity']) && is_numeric($product['price_usd']) ? ($product['quantity'] * $product['price_usd']) : 0;
        $sheet->setCellValue("Q$row", $total);
        $sheet->setCellValue("R$row", $product['comments']);
        $row++;
        $serial++;
    }

    foreach (range('A', 'R') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('R')->setWidth(30);

    $darkGreen = '4B612C';
    $gray = 'D9D9D9';
    $sheet->getStyle("I$subHeaderRow:K$subHeaderRow")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($gray);
    for ($r = 9; $r < $row; $r++) { // Start from row 9 (first data row)
        $sheet->getStyle("I$r:K$r")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($gray);
    }

    $lastDataRow = $row - 1;
    // Apply borders to the entire data range including headers and subheaders
    $sheet->getStyle("A$headerRow:R$lastDataRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));


    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="quotation_' . ($quotation['quotation_number'] ?? 'Export') . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/export_quotation_excel_error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    exit('Error generating Excel file. Please check the error log for details.');
}
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../include/PHPMailer-master/src/Exception.php';
require '../../include/PHPMailer-master/src/PHPMailer.php';
require '../../include/PHPMailer-master/src/SMTP.php';

include '../../config/config.php';

/** @var PDO $conn */
// $database = new Database();
global $conn;

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $quotation_id = isset($_POST['quotation_id']) ? intval($_POST['quotation_id']) : 0;
    $recipient_email = isset($_POST['recipient_email']) ? $_POST['recipient_email'] : '';
    $subject = isset($_POST['email_subject']) ? $_POST['email_subject'] : '';
    $message = isset($_POST['email_message']) ? $_POST['email_message'] : '';
    $attach_pdf = isset($_POST['attach_pdf']) ? true : false;
    $attach_excel = isset($_POST['attach_excel']) ? true : false;
    
    // Validate inputs
    if (empty($quotation_id) || empty($recipient_email) || empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    // Get quotation details from database
    $query = "SELECT * FROM quotations WHERE id = :quotation_id";
    /** @var PDOStatement $stmt */
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':quotation_id', $quotation_id, PDO::PARAM_INT);
    $stmt->execute();
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quotation) {
        echo json_encode(['success' => false, 'message' => 'Quotation not found']);
        exit;
    }
    
    // Get product details for the quotation
    $product_query = "SELECT * FROM quotation_products WHERE quotation_id = :quotation_id";
    /** @var PDOStatement $product_stmt */
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bindParam(':quotation_id', $quotation_id, PDO::PARAM_INT);
    $product_stmt->execute();
    $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare attachments
    $attachments = [];
    
    // Remove PDF attachment generation and attachment
    // if ($attach_pdf) {
    //     // Ensure temp directory exists
    //     $temp_dir = __DIR__ . "/temp";
    //     if (!is_dir($temp_dir)) {
    //         mkdir($temp_dir, 0777, true);
    //     }
    //     // Generate PDF file
    //     $pdf_path = $temp_dir . "/quotation_{$quotation_id}.pdf";
    //     generatePDF($quotation_id, $pdf_path);
    //     $attachments[] = ['path' => $pdf_path, 'name' => "Quotation_{$quotation['quotation_number']}.pdf"];
    // }
    
    if ($attach_excel) {
        // Ensure temp directory exists
        $temp_dir = __DIR__ . "/temp";
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }
        // Generate Excel file as XLSX with images embedded
        $excel_path = $temp_dir . "/quotation_{$quotation_id}.xlsx";
        generateExcelXLSX($quotation, $products, $excel_path);
        $attachments[] = ['path' => $excel_path, 'name' => "Quotation_{$quotation['quotation_number']}.xlsx"];
    }
    
    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'crm@thepurewood.com';
        $mail->Password   = 'Rusty@2014';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        //Recipients
        $mail->setFrom('crm@thepurewood.com', 'Purewood CRM');
        $mail->addAddress($recipient_email);
        
        //Attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                $mail->addAttachment($attachment['path'], $attachment['name']);
            }
        }
        
        //Content
        $mail->isHTML(true);
        $mail->Subject = "Purewood Quotation (" . $quotation['quotation_number'] . ")";
        $mail->Body    = buildEmailTemplate($quotation, $products, $message);
        
        $mail->send();
        
        // Clean up temporary files
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                unlink($attachment['path']);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    } catch (Exception $e) {
        // Return detailed error message for debugging
        echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}. Exception: " . $e->getMessage()]);
    }
    exit;
}

// Function to generate PDF (you'll need to implement this based on your requirements)
function generatePDF($quotation_id, $output_path) {
    // Implementation depends on your PDF generation library
    // For example, using TCPDF or FPDF
    
    // Placeholder implementation - you'll need to replace this
    $pdf_content = "This is a sample PDF for quotation {$quotation_id}";
    file_put_contents($output_path, $pdf_content);
    
    return true;
}

// Function to generate Excel CSV for compatibility
function generateExcelCSV($quotation, $products, $output_path) {
    $header = ['Field', 'Value', 'Field', 'Value'];
    $rows = [];

    // Seller information
    $seller_info = [
        ['Seller Details', '', 'Quotation Details', ''],
        ['Purewood', '', ''],
        ['G178 Special Economy Area (SEZ)', '', ''],
        ['Export Promotion Industrial Park (EPIP)', '', ''],
        ['Boranada, Jodhpur, Rajasthan', '', ''],
        ['India (342001) GST: 08AAQFP4054K1ZQ', '', ''],
        ['', '', '', ''],
    ];

    foreach ($seller_info as $line) {
        $rows[] = $line;
    }

    // Quotation details excluding certain fields
    $exclude_fields = ['id', 'lead_id', 'created_at', 'updated_at'];
    $buyer_details = [];
    foreach ($quotation as $key => $value) {
        if (!in_array($key, $exclude_fields)) {
            $buyer_details[] = [$key, $value];
        }
    }

    // Add buyer details in two columns
    $count = 0;
    $row = [];
    foreach ($buyer_details as $detail) {
        $row = array_merge($row, $detail);
        $count++;
        if ($count % 2 == 0) {
            $rows[] = $row;
            $row = [];
        }
    }
    if (!empty($row)) {
        // Fill empty cells if odd number of columns
        $row = array_pad($row, 4, '');
        $rows[] = $row;
    }

    // Add a blank row before products
    $rows[] = ['', '', '', ''];
    $rows[] = ['Product Details', '', '', ''];

    // Add product headers
    $product_headers = ['Sno', 'Image', 'Item Name/Code', 'Description', 'Assembly', 'Item H', 'Item W', 'Item D', 'Box H', 'Box W', 'Box D', 'CBM', 'Wood/Marble Type', 'No of Packet', 'Iron Gauge', 'MDF Finish', 'Quantity', 'Price USD', 'Comments'];
    $rows[] = $product_headers;

    // Add product rows
    $sno = 1;
    foreach ($products as $product) {
        $image_name = $product['product_image_name'] ?? '';
        $rows[] = [
            $sno++,
            $image_name,
            $product['item_name'] ?? '',
            $product['description'] ?? '',
            $product['assembly'] ?? '',
            $product['item_h'] ?? '',
            $product['item_w'] ?? '',
            $product['item_d'] ?? '',
            $product['box_h'] ?? '',
            $product['box_w'] ?? '',
            $product['box_d'] ?? '',
            $product['cbm'] ?? '',
            $product['wood_type'] ?? '',
            $product['no_of_packet'] ?? '',
            $product['iron_gauge'] ?? '',
            $product['mdf_finish'] ?? '',
            $product['quantity'] ?? '',
            $product['price_usd'] ?? '',
            $product['comments'] ?? '',
        ];
    }

    $fp = fopen($output_path, 'w');
    if ($fp === false) {
        return false;
    }
    fputcsv($fp, $header);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    return true;
}

function buildEmailTemplate($quotation, $products, $message) {
    $exclude_fields = ['id', 'lead_id', 'quotation_image', 'created_at', 'updated_at'];

    $baseUrl = "https://.crm.purewood.in@195.35.44.38/public_html";

    // Seller and Buyer details side by side
    $html = "<table width='100%' cellpadding='5' cellspacing='0' border='0'>";
    $html .= "<tr>";

    // Seller Details (left)
    $html .= "<td style='vertical-align: top; width: 50%;'>";
    $html .= "<h2>Seller Details</h2>";
    $html .= "<p>Purewood<br>";
    $html .= "G178 Special Economy Area (SEZ)<br>";
    $html .= "Export Promotion Industrial Park (EPIP)<br>";
    $html .= "Boranada, Jodhpur, Rajasthan<br>";
    $html .= "India (342001) GST: 08AAQFP4054K1ZQ</p>";
    $html .= "</td>";

    // Quotation Details (right) split into two columns
    $html .= "<td style='vertical-align: top; width: 50%;'>";
    $html .= "<h2>Quotation Details</h2>";
    $html .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";

    $keyLabelMap = [
        'quotation_date' => 'Quotation Date',
        'customer_name' => 'Customer Name',
        'quotation_number' => 'Quotation Number',
        'valid_until' => 'Valid Until',
        'total_amount' => 'Total Amount',
        'status' => 'Status',
        'sales_person' => 'Sales Person',
        'payment_terms' => 'Payment Terms',
        'delivery_date' => 'Delivery Date',
        'lead_source' => 'Lead Source',
        // Add more mappings as needed
    ];

    $count = 0;
    $html .= "<tr>";
    foreach ($quotation as $key => $value) {
        if (!in_array($key, $exclude_fields)) {
            $label = $keyLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key));
            $html .= "<th style='background:#f2f2f2;'>" . htmlspecialchars($label) . "</th>";
            $html .= "<td>" . htmlspecialchars($value) . "</td>";
            $count++;
            if ($count % 4 == 0) {
                $html .= "</tr><tr>";
            }
        }
    }
    // Fill empty cells if not multiple of 4
    $mod = $count % 4;
    if ($mod != 0) {
        for ($i = 0; $i < (4 - $mod); $i++) {
            $html .= "<th>&nbsp;</th><td>&nbsp;</td>";
        }
        $html .= "</tr>";
    } else {
        $html .= "</tr>";
    }
    $html .= "</table>";
    $html .= "</td>";

    $html .= "</tr>";
    $html .= "</table>";

    // Product Details Table
    $html .= "<br><h2>Product Details</h2>";
    $html .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    $html .= "<thead style='background-color: #4CAF50; color: white;'>";
    $html .= "<tr>";
    $html .= "<th>Sno</th>";
    //$html .= "<th>Image</th>";
    $html .= "<th>Item Name/Code</th>";
    $html .= "<th>Description</th>";
    $html .= "<th>Assembly</th>";
    $html .= "<th>Item H</th>";
    $html .= "<th>Item W</th>";
    $html .= "<th>Item D</th>";
    $html .= "<th>Box H</th>";
    $html .= "<th>Box W</th>";
    $html .= "<th>Box D</th>";
    $html .= "<th>CBM</th>";
    $html .= "<th>Wood/Marble Type</th>";
    $html .= "<th>No of Packet</th>";
    $html .= "<th>Iron Gauge</th>";
    $html .= "<th>MDF Finish</th>";
    $html .= "<th>Quantity</th>";
    $html .= "<th>Price USD</th>";
    $html .= "<th>Comments</th>";
    $html .= "</tr>";
    $html .= "</thead>";
    $html .= "<tbody>";
    $sno = 1;
    foreach ($products as $product) {
        //$image_path = $baseUrl . "/assets/images/upload/quotation/" . ($product['product_image_name'] ?? '');
        $html .= "<tr>";
        $html .= "<td>{$sno}</td>";
        //$html .= "<td><img src='{$image_path}' alt='Product Image' style='max-width: 100px; max-height: 100px;'></td>";
        $html .= "<td>" . htmlspecialchars($product['item_name'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['description'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['assembly'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['item_h'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['item_w'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['item_d'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['box_h'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['box_w'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['box_d'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['cbm'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['wood_type'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['no_of_packet'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['iron_gauge'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['mdf_finish'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['quantity'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['price_usd'] ?? '') . "</td>";
        $html .= "<td>" . htmlspecialchars($product['comments'] ?? '') . "</td>";
        $html .= "</tr>";
        $sno++;
    }
    $html .= "</tbody>";
    $html .= "</table>";

    // Place message between product details and thank you note
    $html .= "<br><h3>Message</h3><p>" . nl2br(htmlspecialchars($message)) . "</p>";

    $html .= "<br><p>Thank you,<br>Purewood Team</p>";
    return $html;
}

// Function to generate Excel XLSX with images embedded
function generateExcelXLSX($quotation, $products, $output_path) {
    require_once __DIR__ . '/../../vendor/autoload.php';

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Seller Details block
    $sheet->setCellValue('A1', 'Seller Details');
    $sheet->setCellValue('A2', 'Purewood');
    $sheet->setCellValue('A3', 'G178 Special Economy Area (SEZ)');
    $sheet->setCellValue('A4', 'Export Promotion Industrial Park (EPIP)');
    $sheet->setCellValue('A5', 'Boranada, Jodhpur, Rajasthan');
    $sheet->setCellValue('A6', 'India (342001) GST: 08AAQFP4054K1ZQ');

    // Quotation Details block
    $row = 1;
    $col = 4; // D column
    foreach ($quotation as $key => $value) {
        if (!in_array($key, ['id', 'lead_id', 'created_at', 'updated_at'])) {
            $sheet->setCellValueByColumnAndRow($col, $row, $key);
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $value);
            $row++;
        }
    }

    // Product Headers
    $headers = ['Sno', 'Image', 'Item Name/Code', 'Description', 'Assembly', 'Item H', 'Item W', 'Item D', 'Box H', 'Box W', 'Box D', 'CBM', 'Wood/Marble Type', 'No of Packet', 'Iron Gauge', 'MDF Finish', 'Quantity', 'Price USD', 'Comments'];
    $headerRow = $row + 2;
    $col = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($col, $headerRow, $header);
        $col++;
    }

    // Product Rows
    $productRow = $headerRow + 1;
    $serial = 1;
    foreach ($products as $product) {
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $serial);

        // Insert image if available
        if (!empty($product['product_image_name'])) {
            $baseImagePath = realpath(__DIR__ . '/../../assets/images/upload/quotation/');
            $imageFileName = $product['product_image_name'];
            $imagePath = $baseImagePath . DIRECTORY_SEPARATOR . $imageFileName;
            if (file_exists($imagePath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Product Image');
                $drawing->setDescription('Product Image');
                $drawing->setPath($imagePath);
                $drawing->setHeight(50);
                // Convert column number to letter
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cellCoordinate = $columnLetter . $productRow;
                $drawing->setCoordinates($cellCoordinate);
                $drawing->setWorksheet($sheet);
            }
        }
        $col++;

        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['item_name'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['description'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['assembly'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['item_h'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['item_w'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['item_d'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['box_h'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['box_w'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['box_d'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['cbm'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['wood_type'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['no_of_packet'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['iron_gauge'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['mdf_finish'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['quantity'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['price_usd'] ?? '');
        $sheet->setCellValueByColumnAndRow($col++, $productRow, $product['comments'] ?? '');

        $productRow++;
        $serial++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($output_path);
    return true;
}

// If not a POST request, return error
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>

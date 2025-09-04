<?php
session_start();

// Check if supplier is logged in
if (!isset($_SESSION['supplier_id'])) {
    header('Location: login.php');
    exit;
}

$file_path = '../include/inc/quotation_format.xlsx';

if (file_exists($file_path)) {
    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Quotation_Format_Template.xlsx"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    
    // Clear output buffer
    ob_clean();
    flush();
    
    // Read and output file
    readfile($file_path);
    exit;
} else {
    echo "Template file not found.";
}
?>
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

// Check if po_id is provided in the URL
if (!isset($_GET['po_id']) || empty($_GET['po_id'])) {
    die('PO ID is required');
}

// Sanitize and validate the input
$po_id = intval($_GET['po_id']);
global $conn;

try {
    // Fetch main PO data
    $stmt = $conn->prepare("SELECT * FROM po_main WHERE id = :po_id");
    $stmt->execute(['po_id' => $po_id]);
    $po_main = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle case where PO is not found
    if (!$po_main) {
        die('PO not found');
    }

    // Fetch PO items, ordered by their ID
    $stmt = $conn->prepare("SELECT * FROM po_items WHERE po_id = :po_id ORDER BY id ASC");
    $stmt->execute(['po_id' => $po_id]);
    $po_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

/**
 * PDF class extending FPDF to create a custom Purchase Order document.
 */
class PDF extends FPDF {
    
    /**
     * Header method, automatically called by FPDF on each page.
     */
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'PURCHASE ORDER', 0, 1, 'C');
        $this->Ln(2);
    }

    /**
     * Footer method, automatically called by FPDF at the end of each page.
     */
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'This is a Computer Generated Document', 0, 1, 'C');
    }

    /**
     * Draws the main purchase order form.
     * @param array $po_main Main purchase order data.
     * @param array $po_items Array of purchase order items.
     */
    function drawForm($po_main, $po_items) {
        $this->SetFont('Arial', '', 8);

        // --- Header Blocks ---
        // Block 1: Invoice To
        $this->Rect(10, 20, 85, 36);
        $this->SetXY(12, 22);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, 'Invoice To', 0, 1);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(80, 4, "PUREWOOD\nG-178, Boranada Industrial Park\nBORANADA Jodhpur 342012\nRajasthan\nGSTIN/UIN : AAQFP4054K1ZQ\nState Name : RAJASTHAN, Code : 08\nE-Mail : info@purewood.in");

        // Block 2: Voucher Number and Date
        $this->SetXY(95, 20);
        $this->Cell(54, 8, '', 1, 0); // Voucher No. box
        $this->Cell(51, 8, '', 1, 1); // Dated box
        
        $this->SetXY(95.5, 22.5);
        $this->SetFont('Arial', '', 8);
        $this->Cell(20, 4, "Voucher No.", 0, 0);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(34, 4, $po_main['po_number'], 0, 0);
        
        $this->SetFont('Arial', '', 8);
        $this->Cell(15, 4, "Dated", 0, 0);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(34, 4, date('d-M-y', strtotime($po_main['created_at'])), 0, 1);
        
        // Block 3: Mode/Terms of Payment & References
        $this->SetXY(95, 28);
        $this->Cell(105, 8, '', 1, 1); // Mode/Terms of Payment box
        $this->SetXY(95.5, 29.5);
        $this->SetFont('Arial', '', 8);
        $this->Cell(40, 4, "Mode/Terms of Payment:", 0, 1);

        $this->SetXY(95, 36);
        $this->Cell(54, 8, '', 1, 0); // Reference No. & Date box
        $this->Cell(51, 8, '', 1, 1); // Other References box
        $this->SetXY(95.5, 37.5);
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 4, "Reference No. & Date:", 0, 0);
        $this->Cell(54, 4, "Other References:", 0, 1);

        // Block 4: Consignee (Ship to)
        $this->Rect(10, 56, 85, 20);
        $this->SetXY(12, 58);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, 'Consignee (Ship to)', 0, 1);
        
        // Block 5: Dispatch & Destination
        $this->SetXY(95, 56);
        $this->Cell(54, 8, '', 1, 0); // Dispatched through box
        $this->Cell(51, 8, '', 1, 1); // Destination box
        $this->SetXY(95.5, 57.5);
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 4, "Dispatched through:", 0, 0);
        $this->Cell(20, 4, "Destination:", 0, 1);

        // Block 6: Terms of Delivery
        $this->SetXY(95, 64);
        $this->Cell(105, 12, '', 1, 1);
        $this->SetXY(95.5, 65.5);
        $this->Cell(30, 4, "Terms of Delivery:", 0, 1);

        // Block 7: Supplier (Bill from)
        $this->Rect(10, 76, 190, 14);
        $this->SetXY(12, 78);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 4, 'Supplier (Bill from)', 0, 1);

        // --- Items Table ---
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(10, 90);
        $this->Cell(12, 8, "Sl No.", 1, 0, "C");
        $this->Cell(67, 8, "Description of Goods", 1, 0, "C");
        $this->Cell(20, 8, "Due on", 1, 0, "C");
        $this->Cell(20, 8, "Quantity", 1, 0, "C");
        $this->Cell(20, 8, "Rate", 1, 0, "C");
        $this->Cell(11, 8, "per", 1, 0, "C");
        $this->Cell(40, 8, "Amount", 1, 1, "C");

        // Table Rows
        $this->SetFont('Arial', '', 7);
        $maxRows = 8;
        $rowHeight = 8;
        $startY = 98;
        $totalAmount = 0;

        foreach ($po_items as $i => $item) {
            $this->SetXY(10, $startY + $i * $rowHeight);
            $this->Cell(12, $rowHeight, $i + 1, 1, 0, 'C');
            $this->Cell(67, $rowHeight, $item['product_name'], 1, 0, 'L');
            $this->Cell(20, $rowHeight, "", 1, 0, 'C');
            $this->Cell(20, $rowHeight, $item['quantity'], 1, 0, 'C');
            $this->Cell(20, $rowHeight, number_format($item['price'], 2), 1, 0, 'R');
            $this->Cell(11, $rowHeight, "", 1, 0, 'C');
            $this->Cell(40, $rowHeight, number_format($item['total_amount'], 2), 1, 1, 'R');
            $totalAmount += $item['total_amount'];
        }

        // Fill remaining rows with blank cells
        for ($j = count($po_items); $j < $maxRows; $j++) {
            $this->SetXY(10, $startY + $j * $rowHeight);
            $this->Cell(12, $rowHeight, "", 1, 0, 'C');
            $this->Cell(67, $rowHeight, "", 1, 0, 'L');
            $this->Cell(20, $rowHeight, "", 1, 0, 'C');
            $this->Cell(20, $rowHeight, "", 1, 0, 'C');
            $this->Cell(20, $rowHeight, "", 1, 0, 'R');
            $this->Cell(11, $rowHeight, "", 1, 0, 'C');
            $this->Cell(40, $rowHeight, "", 1, 1, 'R');
        }

        // --- Summary and Footer ---
        $this->SetFont('Arial', 'B', 8);
        $this->SetX(10);
        $this->Cell(150, 8, "Total", 1, 0, 'R');
        $this->Cell(40, 8, number_format($totalAmount, 2), 1, 1, 'R');
        
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 4, "E. & O.E", 0, 1, 'R');
        $this->Ln(5);

        // Signatory section
        $this->SetFont('Arial', '', 8);
        $this->Cell(90, 7, "Company's PAN: AAQFP4054K", 0, 0, 'L');
        $this->Cell(100, 7, "For PUREWOOD", 0, 1, 'R');
        
        $this->Ln(5);
        $this->Cell(0, 7, 'Authorised Signatory', 0, 1, 'R');
    }
}

// Generate the PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->drawForm($po_main, $po_items);
$pdf->Output('I', 'PO_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $po_main['po_number']) . '.pdf');
exit;
?>
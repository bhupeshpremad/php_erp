<?php
include_once __DIR__ . '/../../config/config.php';
include_once ROOT_DIR_PATH . 'include/mailer.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$piId = $_POST['pi_id'] ?? null;
$subject = $_POST['email_subject'] ?? 'Proforma Invoice';
$message = $_POST['email_message'] ?? '';
$attachPdf = isset($_POST['attach_pdf']) && $_POST['attach_pdf'] == 1;
$attachExcel = isset($_POST['attach_excel']) && $_POST['attach_excel'] == 1;

if (!$piId) {
    echo json_encode(['success' => false, 'message' => 'PI ID is required']);
    exit;
}

try {
    // $database = new Database();
    // $conn = $database->getConnection();

    global $conn;


    // Fetch PI details and customer email
    $stmt = $conn->prepare("SELECT pi_number, quotation_number FROM pi WHERE pi_id = ?");
    $stmt->execute([$piId]);
    $pi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pi) {
        echo json_encode(['success' => false, 'message' => 'PI not found']);
        exit;
    }

    // Fetch customer email from quotation
    $stmt = $conn->prepare("SELECT customer_email FROM quotations WHERE quotation_number = ?");
    $stmt->execute([$pi['quotation_number']]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quotation || empty($quotation['customer_email'])) {
        echo json_encode(['success' => false, 'message' => 'Customer email not found']);
        exit;
    }

    $toEmail = $quotation['customer_email'];

    $mail = new PHPMailer(true);
    $mail->setFrom('no-reply@purewood.com', 'Purewood');
    $mail->addAddress($toEmail);
    $mail->Subject = $subject;
    $mail->Body = nl2br($message);
    $mail->isHTML(true);

    // Attach PDF
    if ($attachPdf) {
        $pdfPath = __DIR__ . "/export_pi_pdf.php?id=$piId";
        // Generate PDF content
        ob_start();
        include $pdfPath;
        $pdfContent = ob_get_clean();
        $mail->addStringAttachment($pdfContent, "PI_{$pi['pi_number']}.pdf");
    }

    // Attach Excel
    if ($attachExcel) {
        $excelPath = __DIR__ . "/export_pi_excel.php?id=$piId";
        ob_start();
        include $excelPath;
        $excelContent = ob_get_clean();
        $mail->addStringAttachment($excelContent, "PI_{$pi['pi_number']}.xlsx");
    }

    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include the PHPMailer files from the PHPMailer-master folder
require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

/**
 * Send email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message (HTML)
 * @param array $attachments Array of attachments [['path' => 'file/path.pdf', 'name' => 'filename.pdf']]
 * @param string $cc CC email (optional)
 * @param string $bcc BCC email (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendMail($to, $subject, $message, $attachments = [], $cc = '', $bcc = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'crm@thepurewood.com';
        $mail->Password   = 'Rusty@2014';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('crm@thepurewood.com', 'Purewood CRM');
        $mail->addAddress($to);
        
        if (!empty($cc)) {
            $mail->addCC($cc);
        }
        
        if (!empty($bcc)) {
            $mail->addBCC($bcc);
        }

        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment['path'], $attachment['name']);
            }
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
?>
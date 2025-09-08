<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../include/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../include/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../include/PHPMailer-master/src/SMTP.php';

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        // Configure mailer settings here
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.example.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'your_email@example.com';
        $this->mailer->Password = 'your_password';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->setFrom('your_email@example.com', 'Your Name');
    }

    public function sendEmail($to, $subject, $body, $isHtml = true) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML($isHtml);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
            return ['success' => true, 'message' => 'Email sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Email could not be sent. Mailer Error: ' . $this->mailer->ErrorInfo];
        }
    }
}
?>

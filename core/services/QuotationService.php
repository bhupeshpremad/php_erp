<?php
require_once 'crud.php';
require_once 'email.php';
require_once 'export.php';

class QuotationService {
    private $crud;
    private $conn;
    private $emailService;
    private $exportService;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->crud = new CrudService($dbConnection);
        $this->emailService = new EmailService();
        $this->exportService = new ExportService();
    }

    public function createQuotation($data) {
        return $this->crud->create('quotations', $data);
    }

    public function updateQuotation($quotationId, $data) {
        return $this->crud->update('quotations', $data, 'id = ?', [$quotationId]);
    }

    public function getQuotationById($quotationId) {
        $result = $this->crud->read('quotations', '*', 'id = ?', [$quotationId]);
        if ($result['success'] && !empty($result['data'])) {
            return $result['data'][0];
        }
        return null;
    }

    public function sendQuotationEmail($to, $subject, $body) {
        return $this->emailService->sendEmail($to, $subject, $body);
    }

    public function exportQuotationToExcel($data, $filename = 'quotation.xlsx') {
        return $this->exportService->exportToExcel($data, $filename);
    }

    public function exportQuotationToPDF($htmlContent, $filename = 'quotation.pdf') {
        return $this->exportService->exportToPDF($htmlContent, $filename);
    }
}
?>

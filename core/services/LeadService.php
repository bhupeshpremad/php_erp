<?php
require_once 'crud.php';

class LeadService {
    private $crud;
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->crud = new CrudService($dbConnection);
    }

    public function getNextLeadNumber() {
        $currentYear = date('Y');
        $query = "SELECT lead_number FROM leads WHERE lead_number LIKE 'LEAD-$currentYear-%' ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['lead_number'])) {
            $parts = explode('-', $result['lead_number']);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf("LEAD-%s-%04d", $currentYear, $nextNumber);
    }

    public function getLeadById($leadId) {
        $sql = "SELECT * FROM leads WHERE id = ?";
        $result = $this->crud->read('leads', '*', 'id = ?', [$leadId]);
        if ($result['success'] && !empty($result['data'])) {
            return $result['data'][0];
        }
        return null;
    }

    public function createLead($data) {
        return $this->crud->create('leads', $data);
    }

    public function updateLead($leadId, $data) {
        return $this->crud->update('leads', $data, 'id = ?', [$leadId]);
    }

    public function toggleApprove($leadId, $approve) {
        $sql = "UPDATE leads SET approve = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$approve, $leadId]);
            return ['success' => true, 'message' => 'Lead approval status updated.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating approval status: ' . $e->getMessage()];
        }
    }

    public function getStatusHistory($leadId) {
        $sql = "SELECT status_text, status_date, created_at FROM status WHERE lead_id = ? ORDER BY status_date DESC";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$leadId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error fetching status history: ' . $e->getMessage()];
        }
    }

    public function addStatus($leadId, $statusText, $statusDate) {
        $sql = "INSERT INTO status (lead_id, status_text, status_date) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$leadId, $statusText, $statusDate]);
            return ['success' => true, 'message' => 'Status added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding status: ' . $e->getMessage()];
        }
    }
}
?>

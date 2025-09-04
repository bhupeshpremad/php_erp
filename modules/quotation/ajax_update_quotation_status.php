<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

global $conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$quotation_id = $_POST['quotation_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$quotation_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    if ($action === 'approve') {
        // Update quotation approve status
        $stmt = $conn->prepare("UPDATE quotations SET approve = 1 WHERE id = ?");
        $stmt->execute([$quotation_id]);
        
        echo json_encode(['success' => true, 'message' => 'Quotation approved successfully']);
        
    } elseif ($action === 'add_status') {
        $status_date = $_POST['status_date'] ?? null;
        $status_text = $_POST['status_text'] ?? null;
        
        if (!$status_date || !$status_text) {
            echo json_encode(['success' => false, 'message' => 'Status date and text are required']);
            exit;
        }
        
        // Check if quotation_status table exists, create if not
        $stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS quotation_status (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quotation_id INT NOT NULL,
            status_date DATE NOT NULL,
            status_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quotation_id) REFERENCES quotations(id)
        )");
        $stmt->execute();
        
        // Insert status
        $stmt = $conn->prepare("INSERT INTO quotation_status (quotation_id, status_date, status_text) VALUES (?, ?, ?)");
        $stmt->execute([$quotation_id, $status_date, $status_text]);
        
        echo json_encode(['success' => true, 'message' => 'Status added successfully']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
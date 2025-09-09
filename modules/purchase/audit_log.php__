<?php
// Simple audit logging for purchase operations
function logPurchaseAction($action, $jci_number, $details = []) {
    global $conn;
    
    try {
        // Check if audit table exists, create if not
        $stmt = $conn->query("SHOW TABLES LIKE 'purchase_audit_log'");
        if ($stmt->rowCount() === 0) {
            $conn->exec("
                CREATE TABLE purchase_audit_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    user_type VARCHAR(50),
                    action VARCHAR(100),
                    jci_number VARCHAR(100),
                    details JSON,
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
        
        $user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        $user_type = $_SESSION['user_type'] ?? 'unknown';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $conn->prepare("
            INSERT INTO purchase_audit_log 
            (user_id, user_type, action, jci_number, details, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $user_type,
            $action,
            $jci_number,
            json_encode($details),
            $ip_address
        ]);
        
    } catch (Exception $e) {
        // Log error but don't break the main operation
        error_log("Audit log error: " . $e->getMessage());
    }
}
?>
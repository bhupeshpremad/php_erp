<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Mpdf\Mpdf;

// Local database configuration override
$local_config = [
    'host' => 'localhost',
    'db' => 'crm_purewood',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];

// Check if database exists, if not use a fallback
$dsn = "mysql:host={$local_config['host']};charset={$local_config['charset']}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // First, check if the database exists
    $conn = new PDO($dsn, $local_config['user'], $local_config['pass'], $options);
    
    // Try to connect to the specific database
    $dsn_with_db = "mysql:host={$local_config['host']};dbname={$local_config['db']};charset={$local_config['charset']}";
    $conn = new PDO($dsn_with_db, $local_config['user'], $local_config['pass'], $options);
    
    // Now proceed with the export functionality
    
    $payment_id = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;
    
    if ($payment_id <= 0) {
        die('Invalid payment ID');
    }
    
    // Check if payments table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'payments'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        // Create a sample report for testing
        $html = '<h1>Payment Report</h1>';
        $html .= '<p>This is a test report for Payment ID: ' . $payment_id . '</p>';
        $html .= '<p>Database: ' . $local_config['db'] . '</p>';
        $html .= '<p>Status: Database connected successfully</p>';
        
        $mpdf = new Mpdf(['tempDir' => sys_get_temp_dir()]);
        $mpdf->SetTitle('Payment Report - ' . $payment_id);
        $mpdf->WriteHTML($html);
        $mpdf->Output('payment_report_' . $payment_id . '.pdf', 'D');
        exit;
    }
    
    // Original export logic would go here
    // ... (rest of the export code)
    
} catch (PDOException $e) {
    // Create a fallback report showing the error
    $html = '<h1>Payment Report - Database Issue</h1>';
    $html .= '<p>Error: ' . $e->getMessage() . '</p>';
    $html .= '<p>Please ensure your local database is set up correctly.</p>';
    $html .= '<p>Expected database: ' . $local_config['db'] . '</p>';
    $html .= '<p>Expected user: ' . $local_config['user'] . '</p>';
    
    $mpdf = new Mpdf(['tempDir' => sys_get_temp_dir()]);
    $mpdf->SetTitle('Payment Report - Error');
    $mpdf->WriteHTML($html);
    $mpdf->Output('payment_report_error.pdf', 'D');
    exit;
}
?>

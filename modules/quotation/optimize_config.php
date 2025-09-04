<?php
// Optimize PHP settings for large Excel uploads and processing
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 600); // 10 minutes
ini_set('max_input_time', 600);
ini_set('max_input_vars', 10000);
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '50M');
ini_set('max_file_uploads', 1000);

// Enable output buffering for better performance
if (!ob_get_level()) {
    ob_start();
}

// Set MySQL timeout for long operations
if (isset($conn) && $conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 300);
    $conn->exec("SET SESSION wait_timeout = 600");
    $conn->exec("SET SESSION interactive_timeout = 600");
}
?>
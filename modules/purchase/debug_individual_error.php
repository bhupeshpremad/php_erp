<?php
// Debug individual save errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/audit_log.php';

header('Content-Type: application/json');

try {
    echo json_encode([
        'debug' => true,
        'session' => $_SESSION,
        'post_data' => $_POST,
        'files_data' => array_keys($_FILES),
        'config_check' => defined('DB_HOST') ? 'Config loaded' : 'Config not loaded'
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
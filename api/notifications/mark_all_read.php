<?php
session_start();
require_once '../../config/config.php';
require_once '../../core/NotificationSystem.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$userType = $_SESSION['user_type'] ?? 'superadmin';
$userId = $_SESSION['admin_id'] ?? null;

NotificationSystem::init($conn);
$result = NotificationSystem::markAllAsRead($userType, $userId);

echo json_encode(['success' => $result]);
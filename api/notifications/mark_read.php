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

$notificationId = $_POST['id'] ?? null;

if (!$notificationId) {
    http_response_code(400);
    echo json_encode(['error' => 'Notification ID required']);
    exit;
}

NotificationSystem::init($conn);
$result = NotificationSystem::markAsRead($notificationId);

echo json_encode(['success' => $result]);
<?php
// Proxy to reuse BOM module for superadmin
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
	define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
$_SESSION = $_SESSION ?? [];
$_SESSION['user_type'] = $_SESSION['user_type'] ?? 'superadmin';
include ROOT_DIR_PATH . 'operationadmin/bom/index.php';


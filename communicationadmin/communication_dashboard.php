<?php
session_start();
include_once __DIR__ . '/../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
$admin_department = $_SESSION['admin_department'] ?? 'N/A';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'communicationadmin' || $admin_department !== 'communication') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Communication Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Notifications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM notifications");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM admin_users WHERE status = 'approved'");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Communication Management</h6>
                </div>
                <div class="card-body">
                    <p>Communication features will be implemented here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
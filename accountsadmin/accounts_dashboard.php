<?php
session_start();

// Ensure ROOT_DIR_PATH is defined (it should be coming from config.php via index.php)
if (!defined('ROOT_DIR_PATH')) {
    // Fallback if accessed directly or config.php not loaded
    define('ROOT_DIR_PATH', str_replace('\\', '/', dirname(__DIR__)) . '/');
}

// Include config.php. It is essential for BASE_URL and database connection.
include_once ROOT_DIR_PATH . 'config/config.php';

include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
$admin_department = $_SESSION['admin_department'] ?? 'N/A';

// Ensure only accounts admin can access this page
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'accountsadmin' || $admin_department !== 'accounts') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// The sidebar is now included conditionally in header.php
// include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php'; // THIS LINE IS NOW REMOVED
?>

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Accounts Dashboard</h1>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Welcome, Accounts Admin!</h6>
                            </div>
                            <div class="card-body">
                                <p>This is the dashboard for the Accounts Department.</p>
                                <p>You can manage financial records, approvals, and other accounting-related tasks from here.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

    </div>
    <!-- End of Content Wrapper -->
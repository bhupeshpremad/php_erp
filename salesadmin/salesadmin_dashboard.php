<?php
session_start();
include_once __DIR__ . '/../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

// Check if user is logged in as salesadmin
if ($user_type !== 'salesadmin' && $_SESSION['department'] !== 'sales') {
    header('Location: ../index.php');
    exit;
}


?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sales Admin Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM leads");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Quotations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM quotations");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Customers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM customers");
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total PIs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM pi");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Leads</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Lead Number</th>
                                    <th>Contact Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT lead_number, contact_name, approve FROM leads ORDER BY id DESC LIMIT 5");
                                    $leads = $stmt->fetchAll();
                                    foreach ($leads as $lead) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($lead['lead_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($lead['contact_name']) . "</td>";
                                        echo "<td><span class='badge badge-" . ($lead['approve'] ? 'success' : 'warning') . "'>" . ($lead['approve'] ? 'Approved' : 'Pending') . "</span></td>";
                                        echo "</tr>";
                                    }
                                } catch (Exception $e) {
                                    echo "<tr><td colspan='3'>No leads found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="<?= BASE_URL ?>modules/lead/add.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus text-primary"></i> Add New Lead
                        </a>
                        <a href="<?= BASE_URL ?>modules/quotation/add.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-invoice text-success"></i> Create Quotation
                        </a>
                        <a href="<?= BASE_URL ?>modules/customer/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users text-info"></i> View Customers
                        </a>
                        <a href="<?= BASE_URL ?>modules/pi/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-invoice-dollar text-warning"></i> View PIs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>
</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
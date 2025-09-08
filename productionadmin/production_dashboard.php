<?php
session_start();
include_once __DIR__ . '/../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
include_once ROOT_DIR_PATH . 'productionadmin/sidebar.php';
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Production Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Jobs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM jci_main");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Jobs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">BOMs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM bom_main");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) FROM po_main WHERE status = 'Pending'");
                                    echo $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Recent Job Cards</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>JCI Number</th>
                                    <th>BOM Number</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT jci_number, bom_number, created_at FROM jci_main ORDER BY id DESC LIMIT 5");
                                    $jcis = $stmt->fetchAll();
                                    foreach ($jcis as $jci) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($jci['jci_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($jci['bom_number']) . "</td>";
                                        echo "<td>" . date('M d, Y', strtotime($jci['created_at'])) . "</td>";
                                        echo "</tr>";
                                    }
                                } catch (Exception $e) {
                                    echo "<tr><td colspan='3'>No job cards found</td></tr>";
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
                        <a href="<?= BASE_URL ?>modules/jci/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard-list text-primary"></i> View Job Cards
                        </a>
                        <a href="<?= BASE_URL ?>modules/bom/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-success"></i> View BOMs
                        </a>
                        <a href="<?= BASE_URL ?>modules/po/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-cart text-info"></i> View Purchase Orders
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar text-warning"></i> Production Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
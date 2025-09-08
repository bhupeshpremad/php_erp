<?php
session_start();
include_once __DIR__ . '/../config/config.php'; // Changed to include_once
include_once ROOT_DIR_PATH . 'core/NotificationSystem.php'; // Ensure NotificationSystem is available

// Check if buyer is logged in
if (!isset($_SESSION['buyer_id'])) {
    header('Location: login.php');
    exit;
}

$buyer_id = $_SESSION['buyer_id'];
$buyer_name = $_SESSION['buyer_name'];
$company_name = $_SESSION['company_name'];

global $conn;

// Fetch data for buyer dashboard
$totalQuotations = 0;
$activeOrders = 0;
$pendingQuotations = 0;
$totalEarnings = 0;

try {
    // Total Quotations (for this buyer)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM quotations WHERE customer_name = :company_name");
    $stmt->bindValue(':company_name', $company_name);
    $stmt->execute();
    $totalQuotations = $stmt->fetchColumn();

    // Active Orders (PIs for this buyer)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pi p JOIN quotations q ON p.quotation_id = q.id WHERE q.customer_name = :company_name AND p.status = 'Active'");
    $stmt->bindValue(':company_name', $company_name);
    $stmt->execute();
    $activeOrders = $stmt->fetchColumn();

    // Pending Quotations (for this buyer)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM quotations WHERE customer_name = :company_name AND approve = 0");
    $stmt->bindValue(':company_name', $company_name);
    $stmt->execute();
    $pendingQuotations = $stmt->fetchColumn();

    // Total Earnings (this would be payments received by purewood from this buyer)
    // This requires a more complex query, linking payments to sales made to this buyer.
    // For simplicity, let's keep it at 0 or fetch from a hypothetical total_paid column if it existed.
    // $stmt = $conn->prepare("SELECT SUM(amount_paid) FROM payments WHERE buyer_id = :buyer_id"); // Hypothetical
    // $stmt->execute(['buyer_id' => $buyer_id]);
    // $totalEarnings = $stmt->fetchColumn() ?? 0;

} catch (Exception $e) {
    error_log("Buyer Dashboard Data Fetch Error: " . $e->getMessage());
    // Fallback to 0s
}

?>

<?php include '../include/inc/header.php'; ?>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; // This line will now include the new buyer sidebar ?>
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            
            <!-- Main Content -->
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($buyer_name); ?> (<?php echo htmlspecialchars($company_name); ?>)</span>
                                <img class="img-profile rounded-circle" src="../assets/images/undraw_profile_1.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Buyer Dashboard</h1>
                    </div>
                    
                    <div class="row">
                        <!-- Total Quotations Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Quotations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalQuotations; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Active Orders Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Active Orders</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeOrders; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pending Quotations Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Pending Quotations</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingQuotations; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total Earnings Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Total Earnings</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?php echo $totalEarnings; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        
                        <!-- Quick Actions -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <a href="quotation/add.php" class="btn btn-primary btn-block">
                                                <i class="fas fa-plus mr-2"></i>Add Quotation
                                            </a>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <a href="quotation/list.php" class="btn btn-success btn-block">
                                                <i class="fas fa-list mr-2"></i>View Quotations
                                            </a>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <a href="profile.php" class="btn btn-info btn-block">
                                                <i class="fas fa-user mr-2"></i>Update Profile
                                            </a>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <a href="support.php" class="btn btn-warning btn-block">
                                                <i class="fas fa-headset mr-2"></i>Support
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No recent activity found.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <!-- /.container-fluid -->
                
            </div>
            <!-- End of Main Content -->
            
            <!-- Footer -->
            <?php include '../include/inc/footer.php'; ?>
            <!-- End of Footer -->
            
        </div>
        <!-- End of Content Wrapper -->
        
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../include/inc/footer-top.php'; ?>
</body>
</html>

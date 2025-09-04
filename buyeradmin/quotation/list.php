<?php
session_start();
include '../../config/config.php';

// Check if buyer is logged in
if (!isset($_SESSION['buyer_id'])) {
    header('Location: ../login.php');
    exit;
}

$buyer_id = $_SESSION['buyer_id'];
$buyer_name = $_SESSION['buyer_name'];
$company_name = $_SESSION['company_name'];

// Fetch buyer's quotations
try {
    $stmt = $conn->prepare("SELECT * FROM buyer_quotations WHERE buyer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$buyer_id]);
    $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $quotations = [];
    $error = 'Failed to fetch quotations: ' . $e->getMessage();
}
?>

<?php include '../../include/inc/header.php'; ?>

<body id="page-top">
    <style>
        #wrapper {
            display: flex;
            width: 100%;
        }
        #content-wrapper {
            flex: 1;
            overflow-x: hidden;
        }
    </style>
    <div id="wrapper">
        
        <?php include '../sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($buyer_name); ?></span>
                                <img class="img-profile rounded-circle" src="../../assets/images/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>superadmin/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">My Quotations</h1>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Add New Quotation
                        </a>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <!-- Quotations Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quotations List</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($quotations)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-file-invoice fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">No quotations found. <a href="add.php">Create your first quotation</a></p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Quotation #</th>
                                                <th>Details</th>
                                                <th>Date</th>
                                                <th>Valid Until</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($quotations as $quotation): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($quotation['quotation_number']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($quotation['customer_name'] ?? $quotation['quotation_number']); ?></strong>
                                                        <?php if ($quotation['customer_email']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($quotation['customer_email']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('d M Y', strtotime($quotation['quotation_date'])); ?></td>
                                                    <td><span class="text-muted">-</span></td>
                                                    <td>
                                                        <span class="badge badge-secondary">Draft</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-info" onclick="viewQuotation(<?php echo $quotation['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-warning" onclick="editQuotation(<?php echo $quotation['id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteQuotation(<?php echo $quotation['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
    </div>
    
    <!-- View Quotation Modal -->
    <div class="modal fade" id="viewQuotationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quotation Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="quotationDetails">
                    <!-- Quotation details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sb-admin-2.min.js"></script>
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "order": [[ 2, "desc" ]]
            });
        });
        
        function viewQuotation(id) {
            // Load quotation details via AJAX
            $.ajax({
                url: 'view.php',
                method: 'GET',
                data: { id: id },
                success: function(response) {
                    $('#quotationDetails').html(response);
                    $('#viewQuotationModal').modal('show');
                },
                error: function() {
                    alert('Failed to load quotation details');
                }
            });
        }
        
        function editQuotation(id) {
            window.location.href = 'add.php?id=' + id;
        }
        
        function deleteQuotation(id) {
            if (confirm('Are you sure you want to delete this quotation?')) {
                $.ajax({
                    url: 'delete.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete quotation: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Failed to delete quotation');
                    }
                });
            }
        }
    </script>
    
</body>
</html>

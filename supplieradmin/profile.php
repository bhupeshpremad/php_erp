<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['supplier_id'])) {
    header('Location: login.php');
    exit;
}

$supplier_id = $_SESSION['supplier_id'];

// Get supplier data
$stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();
?>

<?php include '../include/inc/header.php'; ?>

<body id="page-top">
    <style>
        #wrapper { display: flex; width: 100%; }
        #content-wrapper { flex: 1; overflow-x: hidden; }
    </style>
    <div id="wrapper">
        
        <?php include 'sidebar.php'; ?>
        
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['supplier_name']); ?></span>
                                <img class="img-profile rounded-circle" src="../assets/images/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
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
                        <h1 class="h3 mb-0 text-gray-800">Profile Settings</h1>
                    </div>
                    
                    <div class="row">
                        <!-- Profile Information -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Company Information</h6>
                                </div>
                                <div class="card-body">
                                    <form id="profileForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Company Name</label>
                                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                                       value="<?php echo htmlspecialchars($supplier['company_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Contact Person</label>
                                                <input type="text" class="form-control" id="contact_person_name" name="contact_person_name" 
                                                       value="<?php echo htmlspecialchars($supplier['contact_person_name']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="contact_person_email" name="contact_person_email" 
                                                       value="<?php echo htmlspecialchars($supplier['contact_person_email']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" id="contact_person_phone" name="contact_person_phone" 
                                                       value="<?php echo htmlspecialchars($supplier['contact_person_phone']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" id="company_address" name="company_address" rows="3" required><?php echo htmlspecialchars($supplier['company_address']); ?></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary" id="updateProfileBtn">
                                            <i class="fas fa-save mr-2"></i>Update Profile
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Password Change -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                                </div>
                                <div class="card-body">
                                    <form id="passwordForm">
                                        <div class="mb-3">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <small class="text-muted">Min 8 chars with uppercase, lowercase, number & special char</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-warning btn-block" id="changePasswordBtn">
                                            <i class="fas fa-key mr-2"></i>Change Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
    </div>
    
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
    
    <script>
        // Profile update
        $('#profileForm').submit(function(e) {
            e.preventDefault();
            
            $('#updateProfileBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');
            
            $.ajax({
                url: 'update_profile.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to update profile');
                },
                complete: function() {
                    $('#updateProfileBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Update Profile');
                }
            });
        });
        
        // Password change
        $('#passwordForm').submit(function(e) {
            e.preventDefault();
            
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();
            
            if (newPassword !== confirmPassword) {
                showToast('error', 'Passwords do not match');
                return;
            }
            
            if (newPassword.length < 8 || !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(newPassword)) {
                showToast('error', 'Password must be at least 8 characters with uppercase, lowercase, number and special character');
                return;
            }
            
            $('#changePasswordBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Changing...');
            
            $.ajax({
                url: 'change_password.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        $('#passwordForm')[0].reset();
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to change password');
                },
                complete: function() {
                    $('#changePasswordBtn').prop('disabled', false).html('<i class="fas fa-key mr-2"></i>Change Password');
                }
            });
        });
        
        function showToast(type, message) {
            const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
            const toast = `
                <div class="toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="toast-header ${bgClass} text-white">
                        <strong class="mr-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                        <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>
            `;
            $('body').append(toast);
            $('.toast').toast({delay: 3000}).toast('show').on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
    </script>
    
</body>
</html>
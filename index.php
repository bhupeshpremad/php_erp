<?php
session_start();
include './config/config.php';
include './core/auth.php';
include './include/inc/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $auth = new Auth();

        // Try superadmin login first
        if ($auth->login('superadmin', $email, $password)) {
            $_SESSION['user_type'] = 'superadmin';
            $_SESSION['user_email'] = $email;
            header('Location: ' . BASE_URL . 'superadmin/superadmin_dashboard.php');
            exit();
        } else {
            // Check static communication admin login
            if ($email === 'communicationadmin' && $password === 'Admin@123') {
                $_SESSION['user_type'] = 'communicationadmin';
                $_SESSION['user_email'] = $email;
                $_SESSION['username'] = 'Communication Admin';
                header('Location: ' . BASE_URL . 'communicationadmin/communication_dashboard.php');
                exit();
            }
            
            // Try department-based login
            $departments = ['sales', 'accounts', 'operation', 'production'];
            $loginSuccess = false;
            
            foreach ($departments as $dept) {
                if ($auth->login($dept, $email, $password)) {
                    $_SESSION['user_type'] = $dept . 'admin';
                    $_SESSION['user_email'] = $email;
                    $_SESSION['department'] = $dept;
                    $loginSuccess = true;
                    
                    // Redirect to appropriate dashboard
                    $dashboards = [
                        'sales' => 'salesadmin/salesadmin_dashboard.php',
                        'accounts' => 'accountsadmin/accounts_dashboard.php', 
                        'operation' => 'operationadmin/operation_dashboard.php',
                        'production' => 'productionadmin/production_dashboard.php'
                    ];
                    
                    $dashboard = $dashboards[$dept] ?? 'modules/lead/index.php';
                    header('Location: ' . BASE_URL . $dashboard);
                    exit();
                    break;
                }
            }
            
            // Try buyer login
            if ($auth->login('buyer', $email, $password)) {
                $_SESSION['user_type'] = 'buyer';
                $_SESSION['user_email'] = $email;
                $loginSuccess = true; // Set to true if buyer login is successful
                header('Location: ' . BASE_URL . 'buyeradmin/dashboard.php');
                exit();
            }

            if (!$loginSuccess) {
                $error = 'Invalid email or password. Please check your credentials and ensure your account is approved.';
            }
        }
    }
}
?>

<div class="container-fluid bg-gradient-primary" style="min-height: 100vh;">
    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center align-items-center mt-5">
    
            <div class="col-xl-10 col-lg-12 col-md-9 mt-5">
    
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row align-items-center">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image text-center">
                               <img src="./assets/images/Purewood-Joey Logo.png" alt="" class="img-fluid" style="width: 70%; height: auto;">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                     <?php if ($error): ?>
                                        <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 100px;">
                                            <div style="position: absolute; top: 0; right: 0; z-index: 1080;">
                                                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                                                    <div class="toast-header bg-danger text-white">
                                                        <strong class="mr-auto">Error</strong>
                                                        <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="toast-body">
                                                        <?php echo htmlspecialchars($error); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                         <script>
                                             document.addEventListener('DOMContentLoaded', function () {
                                                 $('.toast').toast('show');
                                             });
                                         </script>
                                    <?php endif; ?>
                                     <form class="user" method="POST" action="">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control form-control-user"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address..." required>
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control form-control-user"
                                                    id="exampleInputPassword" placeholder="Password" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border-radius: 0 50px 50px 0;">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <hr>
                                     </form>

                                    <div class="text-center">
                                        <a class="small" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="admin_register.php">Admin Registration</a>
                                    </div>
                                    <div class="text-center mt-2">
                                        <a class="small" href="supplieradmin/login.php">Supplier Login</a> | 
                                        <a class="small" href="supplieradmin/register.php">Supplier Registration</a>
                                    </div>
                                    <div class="text-center mt-2">
                                        <a class="small" href="buyer_register.php">Buyer Registration</a> | 
                                        <a class="small" href="buyeradmin/login.php">Buyer Login</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
            </div>
    
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm_admin">
                        <div class="mb-3">
                            <label for="reset_email_admin" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="reset_email_admin" required>
                        </div>
                        <input type="hidden" name="user_role" value="admin">
                        <button type="submit" class="btn btn-primary">Send OTP</button>
                    </form>
                    
                    <div id="otpSection_admin" style="display: none;">
                        <hr>
                        <form id="otpForm_admin">
                            <div class="mb-3">
                                <label for="otp_admin" class="form-label">Enter OTP</label>
                                <input type="text" class="form-control" id="otp_admin" maxlength="6" required>
                            </div>
                            <input type="hidden" name="user_role" value="admin">
                            <button type="submit" class="btn btn-success">Verify OTP</button>
                        </form>
                    </div>
                    
                    <div id="resetPasswordSection_admin" style="display: none;">
                        <hr>
                        <form id="resetPasswordForm_admin">
                            <div class="mb-3">
                                <label for="new_password_admin" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password_admin" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password_admin" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_new_password_admin" required>
                            </div>
                            <input type="hidden" name="user_role" value="admin">
                            <button type="submit" class="btn btn-success">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include './include/inc/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle password visibility
        var toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                var passwordField = document.getElementById('exampleInputPassword');
                var icon = this.querySelector('i');
                if (!passwordField) return;
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
                } else {
                    passwordField.type = 'password';
                    if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
                }
            });
        }



        // Forgot password functionality for Admin
        document.getElementById('forgotPasswordForm_admin').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email_admin').value;
            
            fetch('forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=send_otp&user_role=admin&email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('otpSection_admin').style.display = 'block';
                    alert('OTP sent to your email');
                } else {
                    alert(data.message);
                }
            });
        });
        
        document.getElementById('otpForm_admin').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email_admin').value;
            const otp = document.getElementById('otp_admin').value;
            
            fetch('forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=verify_otp&user_role=admin&email=' + encodeURIComponent(email) + '&otp=' + otp
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resetPasswordSection_admin').style.display = 'block';
                    alert('OTP verified successfully');
                } else {
                    alert(data.message);
                }
            });
        });
        
        document.getElementById('resetPasswordForm_admin').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email_admin').value;
            const newPassword = document.getElementById('new_password_admin').value;
            const confirmPassword = document.getElementById('confirm_new_password_admin').value;
            
            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            if (newPassword.length < 8 || !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(newPassword)) {
                alert('Password must be at least 8 characters with uppercase, lowercase, number and special character');
                return;
            }
            
            fetch('forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset_password&user_role=admin&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(newPassword)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password reset successfully');
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        });

    });
</script>
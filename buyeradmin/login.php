<?php
session_start();
include '../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, contact_person_name, company_name, password, status FROM buyers WHERE contact_person_email = ? AND status = 'approved'");
            $stmt->execute([$email]);
            $buyer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($buyer && password_verify($password, $buyer['password'])) {
                $_SESSION['buyer_id'] = $buyer['id'];
                $_SESSION['buyer_name'] = $buyer['contact_person_name'];
                $_SESSION['company_name'] = $buyer['company_name'];
                $_SESSION['buyer_email'] = $email;
                $_SESSION['user_type'] = 'buyer';
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password. Please check your credentials and ensure your account is approved.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Login - Purewood ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-login-image {
            background-position: center;
            background-size: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid bg-gradient-primary" style="min-height: 100vh;">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-xl-10 col-lg-12 col-md-9 mt-5">
                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <div class="card-body p-0">
                            <div class="row align-items-center">
                                <div class="col-lg-6 d-none d-lg-block bg-login-image text-center">
                                    <img src="../assets/images/Purewood-Joey Logo.png" alt="" class="img-fluid" style="width: 70%; height: auto;">
                                </div>
                                <div class="col-lg-6">
                                    <div class="p-5">
                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-4">Buyer Login</h1>
                                        </div>
                                        <?php if ($error): ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?php echo htmlspecialchars($error); ?>
                                            </div>
                                        <?php endif; ?>
                                        <form class="user" method="POST" action="">
                                            <div class="form-group">
                                                <input type="email" name="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..." required>
                                            </div>
                                            <div class="form-group">
                                                <input type="password" name="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                                Login
                                            </button>
                                            <hr>
                                        </form>
                                        <div class="text-center">
                                            <a class="small" href="../index.php">Back to Main Login</a>
                                        </div>
                                        <div class="text-center">
                                            <a class="small" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                                            <br>
                                            <a class="small" href="../buyer_register.php">Create an Account!</a>
                                        </div>
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
                    <form id="forgotPasswordForm_buyer">
                        <div class="mb-3">
                            <label for="reset_email_buyer" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="reset_email_buyer" required>
                        </div>
                        <input type="hidden" name="user_role" value="buyer">
                        <button type="submit" class="btn btn-primary">Send OTP</button>
                    </form>
                    
                    <div id="otpSection_buyer" style="display: none;">
                        <hr>
                        <form id="otpForm_buyer">
                            <div class="mb-3">
                                <label for="otp_buyer" class="form-label">Enter OTP</label>
                                <input type="text" class="form-control" id="otp_buyer" maxlength="6" required>
                            </div>
                            <input type="hidden" name="user_role" value="buyer">
                            <button type="submit" class="btn btn-success">Verify OTP</button>
                        </form>
                    </div>
                    
                    <div id="resetPasswordSection_buyer" style="display: none;">
                        <hr>
                        <form id="resetPasswordForm_buyer">
                            <div class="mb-3">
                                <label for="new_password_buyer" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password_buyer" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password_buyer" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_new_password_buyer" required>
                            </div>
                            <input type="hidden" name="user_role" value="buyer">
                            <button type="submit" class="btn btn-success">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('exampleInputPassword');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Forgot password functionality for Buyer
        document.getElementById('forgotPasswordForm_buyer').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email_buyer').value;
            
            fetch('../forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=send_otp&user_role=buyer&email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('otpSection_buyer').style.display = 'block';
                    alert('OTP sent to your email');
                } else {
                    alert(data.message);
                }
            });
        });
        
        document.getElementById('otpForm_buyer').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email_buyer').value;
            const otp = document.getElementById('otp_buyer').value;
            
            fetch('../forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=verify_otp&user_role=buyer&email=' + encodeURIComponent(email) + '&otp=' + otp
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resetPasswordSection_buyer').style.display = 'block';
                    alert('OTP verified successfully');
                } else {
                    alert(data.message);
                }
            });
        });
        
        document.getElementById('resetPasswordForm_buyer').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email_buyer').value;
            const newPassword = document.getElementById('new_password_buyer').value;
            const confirmPassword = document.getElementById('confirm_new_password_buyer').value;
            
            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            if (newPassword.length < 8 || !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(newPassword)) {
                alert('Password must be at least 8 characters with uppercase, lowercase, number and special character');
                return;
            }
            
            fetch('../forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset_password&user_role=buyer&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(newPassword)
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

    </script>
</body>
</html>

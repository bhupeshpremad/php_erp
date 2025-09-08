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
            $stmt = $conn->prepare("SELECT id, company_name, contact_person_name, password, email_verified, status FROM suppliers WHERE contact_person_email = ?");
            $stmt->execute([$email]);
            $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($supplier && password_verify($password, $supplier['password'])) {
                if ($supplier['email_verified'] == 0) {
                    $error = 'Please verify your email first. Check your inbox for verification link.';
                } elseif ($supplier['status'] !== 'active') {
                    $error = 'Your account is not active. Please contact admin.';
                } else {
                    // Login successful
                    $_SESSION['supplier_id'] = $supplier['id'];
                    $_SESSION['supplier_name'] = $supplier['contact_person_name'];
                    $_SESSION['company_name'] = $supplier['company_name'];
                    $_SESSION['supplier_email'] = $email;
                    
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
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
    <title>Supplier Login - Purewood ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2 class="mb-2">Supplier Login</h2>
                <p class="mb-0">Access your supplier portal</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                    <br>
                    <a href="register.php" class="text-decoration-none">New supplier? Register here</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="reset_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="reset_email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Send OTP</button>
                    </form>
                    
                    <div id="otpSection" style="display: none;">
                        <hr>
                        <form id="otpForm">
                            <div class="mb-3">
                                <label for="otp" class="form-label">Enter OTP</label>
                                <input type="text" class="form-control" id="otp" maxlength="6" required>
                            </div>
                            <button type="submit" class="btn btn-success">Verify OTP</button>
                        </form>
                    </div>
                    
                    <div id="resetPasswordSection" style="display: none;">
                        <hr>
                        <form id="resetPasswordForm">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_new_password" required>
                            </div>
                            <button type="submit" class="btn btn-success">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
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

        // Forgot password functionality
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email').value;
            
            fetch('../forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=send_otp&email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('otpSection').style.display = 'block';
                    alert('OTP sent to your email');
                } else {
                    alert(data.message);
                }
            });
        });
        
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email').value;
            const otp = document.getElementById('otp').value;
            
            fetch('../forgot_password_handler.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=verify_otp&email=' + encodeURIComponent(email) + '&otp=' + otp
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resetPasswordSection').style.display = 'block';
                    alert('OTP verified successfully');
                } else {
                    alert(data.message);
                }
            });
        });
        
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('reset_email').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_new_password').value;
            
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
                body: 'action=reset_password&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(newPassword)
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
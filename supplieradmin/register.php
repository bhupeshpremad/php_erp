<?php
session_start();
include '../config/config.php';
include '../core/email_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name'] ?? '');
    $company_address = trim($_POST['company_address'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $gstin = strtoupper(trim($_POST['gstin'] ?? ''));
    $contact_person_name = trim($_POST['contact_person_name'] ?? '');
    $contact_person_phone = trim($_POST['contact_person_phone'] ?? '');
    $contact_person_email = trim($_POST['contact_person_email'] ?? '');
    $contract_signed = $_POST['contract_signed'] ?? 'no';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms_accepted = isset($_POST['terms_accepted']);

    // Validation
    if (empty($company_name) || empty($company_address) || empty($country) || empty($state) || 
        empty($city) || empty($zip_code) || empty($gstin) || empty($contact_person_name) || 
        empty($contact_person_phone) || empty($contact_person_email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($contact_person_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin)) {
        $error = 'Invalid GSTIN format.';
    } elseif (!preg_match('/^[0-9]{10}$/', $contact_person_phone)) {
        $error = 'Phone number must be 10 digits.';
    } elseif (strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
        $error = 'Password must be at least 8 characters with uppercase, lowercase, number and special character.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!$terms_accepted) {
        $error = 'Please accept terms and conditions.';
    } else {
        try {
            // Check if email or GSTIN already exists
            $stmt = $conn->prepare("SELECT id FROM suppliers WHERE contact_person_email = ? OR gstin = ?");
            $stmt->execute([$contact_person_email, $gstin]);
            if ($stmt->fetch()) {
                $error = 'Email or GSTIN already registered.';
            } else {
                // Generate verification token
                $verification_token = bin2hex(random_bytes(32));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert supplier
                $stmt = $conn->prepare("INSERT INTO suppliers (company_name, company_address, country, state, city, zip_code, gstin, contact_person_name, contact_person_phone, contact_person_email, contract_signed, password, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$company_name, $company_address, $country, $state, $city, $zip_code, $gstin, $contact_person_name, $contact_person_phone, $contact_person_email, $contract_signed, $hashed_password, $verification_token]);
                
                if ($result) {
                    // Send verification emails
                    $verification_link = BASE_URL . "supplieradmin/verify.php?token=" . $verification_token;
                    
                    // Email to supplier
                    $supplier_subject = "Registration Successful - Purewood ERP";
                    $supplier_message = "Dear $contact_person_name,\n\nThank you for registering as a supplier with Purewood ERP.\n\nYour registration has been completed successfully. Our team will review your application and contact you soon.\n\nCompany Details:\nCompany Name: $company_name\nContact Person: $contact_person_name\nEmail: $contact_person_email\nPhone: $contact_person_phone\nGSTIN: $gstin\n\nPlease verify your email by clicking the link below:\n$verification_link\n\nBest regards,\nPurewood Team";
                    sendEmail($contact_person_email, $supplier_subject, $supplier_message, $contact_person_name);
                    
                    // Email to super admin
                    $admin_email = "pbjods@gmail.com";
                    $admin_subject = "New Supplier Registration - $company_name";
                    $admin_message = "Dear Admin,\n\nA new supplier has registered on Purewood ERP.\n\nSupplier Details:\nCompany Name: $company_name\nContact Person: $contact_person_name\nEmail: $contact_person_email\nPhone: $contact_person_phone\nGSTIN: $gstin\nAddress: $company_address, $city, $state, $country - $zip_code\nContract Signed: $contract_signed\n\nRegistration Date: " . date('Y-m-d H:i:s') . "\n\nVerification Link: $verification_link\n\nPlease review and approve the supplier.\n\nBest regards,\nPurewood ERP System";
                    sendEmail($admin_email, $admin_subject, $admin_message, 'Super Admin');
                    
                    $success = 'Registration successful! Please check your email for confirmation.';
                    // Redirect to index.php after 3 seconds
                    echo '<script>setTimeout(function(){ window.location.href = "../index.php"; }, 3000);</script>';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log('Supplier Registration Error: ' . $e->getMessage());
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Registration - Purewood ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .registration-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .registration-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .form-section {
            padding: 2rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .password-strength {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="registration-card">
                        <div class="registration-header">
                            <h2 class="mb-2">Supplier Registration</h2>
                            <p class="mb-0">Join our supplier network</p>
                        </div>
                        
                        <div class="form-section">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="supplierForm" novalidate>
                                <!-- Row 1: Company Name & Company Address -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Company name is required.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company_address" class="form-label">Company Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="company_address" name="company_address" rows="2" required><?php echo htmlspecialchars($_POST['company_address'] ?? ''); ?></textarea>
                                        <div class="invalid-feedback">Company address is required.</div>
                                    </div>
                                </div>

                                <!-- Row 2: Country & State -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                        <select class="form-select" id="country" name="country" required>
                                            <option value="">Select Country</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a country.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                        <select class="form-select" id="state" name="state" required>
                                            <option value="">Select State</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a state.</div>
                                    </div>
                                </div>

                                <!-- Row 3: City & Zip Code -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                        <select class="form-select" id="city" name="city" required>
                                            <option value="">Select City</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a city.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zip_code" class="form-label">Zip Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                               value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Zip code is required.</div>
                                    </div>
                                </div>

                                <!-- Row 4: GSTIN & Contact Person Name -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="gstin" class="form-label">Company GSTIN <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="gstin" name="gstin" 
                                               placeholder="22AAAAA0000A1Z5" maxlength="15" style="text-transform: uppercase;"
                                               value="<?php echo htmlspecialchars($_POST['gstin'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Valid GSTIN is required.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_person_name" class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="contact_person_name" name="contact_person_name" 
                                               value="<?php echo htmlspecialchars($_POST['contact_person_name'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Contact person name is required.</div>
                                    </div>
                                </div>

                                <!-- Row 5: Phone & Email -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contact_person_phone" class="form-label">Contact Person Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="contact_person_phone" name="contact_person_phone" 
                                               placeholder="9876543210" maxlength="10"
                                               value="<?php echo htmlspecialchars($_POST['contact_person_phone'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Valid 10-digit phone number is required.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_person_email" class="form-label">Contact Person Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="contact_person_email" name="contact_person_email" 
                                               value="<?php echo htmlspecialchars($_POST['contact_person_email'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Valid email is required.</div>
                                    </div>
                                </div>

                                <!-- Row 6: Contract Signed & Password -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contract_signed" class="form-label">Contract Signed <span class="text-danger">*</span></label>
                                        <select class="form-select" id="contract_signed" name="contract_signed" required>
                                            <option value="no" <?php echo ($_POST['contract_signed'] ?? '') === 'no' ? 'selected' : ''; ?>>No</option>
                                            <option value="yes" <?php echo ($_POST['contract_signed'] ?? '') === 'yes' ? 'selected' : ''; ?>>Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength" id="passwordStrength"></div>
                                        <div class="invalid-feedback">Password must be at least 8 characters with uppercase, lowercase, number and special character.</div>
                                    </div>
                                </div>

                                <!-- Row 7: Confirm Password -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div class="invalid-feedback">Passwords must match.</div>
                                    </div>
                                </div>

                                <!-- Terms & Conditions -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" required>
                                        <label class="form-check-label" for="terms_accepted">
                                            I accept the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> <span class="text-danger">*</span>
                                        </label>
                                        <div class="invalid-feedback">You must accept the terms and conditions.</div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Register Supplier
                                    </button>
                                </div>
                            </form>
                            
                            <div class="text-center mt-3">
                                <a href="login.php" class="text-decoration-none">Already registered? Login here</a> |
                                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>By registering as a supplier, you agree to:</p>
                    <ul>
                        <li>Provide accurate and complete information</li>
                        <li>Maintain quality standards as per company requirements</li>
                        <li>Comply with all applicable laws and regulations</li>
                        <li>Maintain confidentiality of business information</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('supplierForm');
            const countrySelect = document.getElementById('country');
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');
            
            // Load countries
            loadCountries();
            
            // Country change event
            countrySelect.addEventListener('change', function() {
                const countryCode = this.value;
                if (countryCode) {
                    loadStates(countryCode);
                } else {
                    clearSelect(stateSelect);
                    clearSelect(citySelect);
                }
            });
            
            // State change event
            stateSelect.addEventListener('change', function() {
                const countryCode = countrySelect.value;
                const stateCode = this.value;
                if (countryCode && stateCode) {
                    loadCities(countryCode, stateCode);
                } else {
                    clearSelect(citySelect);
                }
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
            
            // GSTIN validation
            document.getElementById('gstin').addEventListener('input', function() {
                this.value = this.value.toUpperCase();
                validateGSTIN(this.value);
            });
            
            // Phone validation
            document.getElementById('contact_person_phone').addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });
            
            // Password strength
            document.getElementById('password').addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            
            // Confirm password validation
            document.getElementById('confirm_password').addEventListener('input', function() {
                const password = document.getElementById('password').value;
                if (this.value !== password) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
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
                
                if (!validatePassword(newPassword)) {
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
            
            function loadCountries() {
                fetch('https://api.countrystatecity.in/v1/countries', {
                    headers: {'X-CSCAPI-KEY': 'NHhvOEcyWk50N2Vna3VFTE00bFp3MjFKR0ZEOUhkZlg4RTk1MlJlaA=='}
                })
                .then(response => response.json())
                .then(data => {
                    countrySelect.innerHTML = '<option value="">Select Country</option>';
                    data.forEach(country => {
                        const option = document.createElement('option');
                        option.value = country.iso2;
                        option.textContent = country.name;
                        countrySelect.appendChild(option);
                    });
                })
                .catch(() => {
                    const basicCountries = [{iso2: 'IN', name: 'India'}];
                    countrySelect.innerHTML = '<option value="">Select Country</option>';
                    basicCountries.forEach(country => {
                        const option = document.createElement('option');
                        option.value = country.iso2;
                        option.textContent = country.name;
                        countrySelect.appendChild(option);
                    });
                });
            }
            
            function loadStates(countryCode) {
                fetch(`https://api.countrystatecity.in/v1/countries/${countryCode}/states`, {
                    headers: {'X-CSCAPI-KEY': 'NHhvOEcyWk50N2Vna3VFTE00bFp3MjFKR0ZEOUhkZlg4RTk1MlJlaA=='}
                })
                .then(response => response.json())
                .then(data => {
                    stateSelect.innerHTML = '<option value="">Select State</option>';
                    data.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state.iso2;
                        option.textContent = state.name;
                        stateSelect.appendChild(option);
                    });
                    clearSelect(citySelect);
                });
            }
            
            function loadCities(countryCode, stateCode) {
                fetch(`https://api.countrystatecity.in/v1/countries/${countryCode}/states/${stateCode}/cities`, {
                    headers: {'X-CSCAPI-KEY': 'NHhvOEcyWk50N2Vna3VFTE00bFp3MjFKR0ZEOUhkZlg4RTk1MlJlaA=='}
                })
                .then(response => response.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Select City</option>';
                    data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.name;
                        option.textContent = city.name;
                        citySelect.appendChild(option);
                    });
                });
            }
            
            function clearSelect(selectElement) {
                const name = selectElement.name.charAt(0).toUpperCase() + selectElement.name.slice(1);
                selectElement.innerHTML = `<option value="">Select ${name}</option>`;
            }
            
            function validateGSTIN(gstin) {
                const pattern = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
                const input = document.getElementById('gstin');
                
                if (gstin.length === 15 && pattern.test(gstin)) {
                    input.setCustomValidity('');
                } else if (gstin.length > 0) {
                    input.setCustomValidity('Invalid GSTIN format');
                } else {
                    input.setCustomValidity('');
                }
            }
            
            function checkPasswordStrength(password) {
                const strengthDiv = document.getElementById('passwordStrength');
                let strength = 0;
                let feedback = '';
                
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[@$!%*?&]/.test(password)) strength++;
                
                if (strength < 3) {
                    feedback = 'Weak password';
                    strengthDiv.className = 'password-strength strength-weak';
                } else if (strength < 5) {
                    feedback = 'Medium password';
                    strengthDiv.className = 'password-strength strength-medium';
                } else {
                    feedback = 'Strong password';
                    strengthDiv.className = 'password-strength strength-strong';
                }
                
                strengthDiv.textContent = feedback;
            }
            
            function validatePassword(password) {
                return password.length >= 8 && 
                       /[a-z]/.test(password) && 
                       /[A-Z]/.test(password) && 
                       /\d/.test(password) && 
                       /[@$!%*?&]/.test(password);
            }
        });
    </script>
</body>
</html>
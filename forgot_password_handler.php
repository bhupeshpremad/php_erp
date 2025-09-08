<?php
session_start();
include './config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_otp':
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = 'Valid email is required.';
                break;
            }

            $user_role = $_POST['user_role'] ?? 'supplier'; // Default to supplier if not provided
            $user_table = '';
            $otp_table = '';
            $email_column = '';

            switch ($user_role) {
                case 'admin':
                    $user_table = 'admin_users';
                    $otp_table = 'admin_otps';
                    $email_column = 'email';
                    break;
                case 'buyer':
                    $user_table = 'buyers';
                    $otp_table = 'buyer_otps';
                    $email_column = 'contact_person_email';
                    break;
                case 'supplier':
                default:
                    $user_table = 'suppliers';
                    $otp_table = 'supplier_otps';
                    $email_column = 'contact_person_email';
                    break;
            }

            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM {$user_table} WHERE {$email_column} = ?");
            $stmt->execute([$email]);
            
            if (!$stmt->fetch()) {
                $response['message'] = 'Email not found in our records.';
                break;
            }
            
            // Generate OTP
            $otp = sprintf('%06d', mt_rand(0, 999999));
            $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Save OTP
            $stmt = $conn->prepare("INSERT INTO {$otp_table} (email, otp, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $otp, $expires_at]);
            
            // Send OTP email
            $subject = "Password Reset OTP - Purewood ERP";
            $message = "Your OTP for password reset is: $otp\n\nThis OTP will expire in 10 minutes.\n\nIf you didn't request this, please ignore this email.";
            
            if (mail($email, $subject, $message)) {
                $response['success'] = true;
                $response['message'] = 'OTP sent successfully.';
            } else {
                $response['message'] = 'Failed to send OTP. Please try again.';
            }
            break;
            
        case 'verify_otp':
            $email = trim($_POST['email'] ?? '');
            $otp = trim($_POST['otp'] ?? '');
            $user_role = $_POST['user_role'] ?? 'supplier';

            $otp_table = '';
            switch ($user_role) {
                case 'admin': $otp_table = 'admin_otps'; break;
                case 'buyer': $otp_table = 'buyer_otps'; break;
                case 'supplier':
                default: $otp_table = 'supplier_otps'; break;
            }
            
            if (empty($email) || empty($otp)) {
                $response['message'] = 'Email and OTP are required.';
                break;
            }
            
            // Verify OTP
            $stmt = $conn->prepare("SELECT id FROM {$otp_table} WHERE email = ? AND otp = ? AND expires_at > NOW() AND used = 0 ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email, $otp]);
            
            if ($stmt->fetch()) {
                // Mark OTP as used
                $stmt = $conn->prepare("UPDATE {$otp_table} SET used = 1 WHERE email = ? AND otp = ?");
                $stmt->execute([$email, $otp]);
                
                $response['success'] = true;
                $response['message'] = 'OTP verified successfully.';
            } else {
                $response['message'] = 'Invalid or expired OTP.';
            }
            break;
            
        case 'reset_password':
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $user_role = $_POST['user_role'] ?? 'supplier';

            $user_table = '';
            $otp_table = '';
            $email_column = '';
            switch ($user_role) {
                case 'admin':
                    $user_table = 'admin_users';
                    $otp_table = 'admin_otps';
                    $email_column = 'email';
                    break;
                case 'buyer':
                    $user_table = 'buyers';
                    $otp_table = 'buyer_otps';
                    $email_column = 'contact_person_email';
                    break;
                case 'supplier':
                default:
                    $user_table = 'suppliers';
                    $otp_table = 'supplier_otps';
                    $email_column = 'contact_person_email';
                    break;
            }

            if (empty($email) || empty($password)) {
                $response['message'] = 'Email and password are required.';
                break;
            }
            
            // Validate password
            if (strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
                $response['message'] = 'Password must be at least 8 characters with uppercase, lowercase, number and special character.';
                break;
            }
            
            // Check if there's a verified OTP for this email
            $stmt = $conn->prepare("SELECT id FROM {$otp_table} WHERE email = ? AND used = 1 AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email]);
            
            if (!$stmt->fetch()) {
                $response['message'] = 'Invalid session. Please request OTP again.';
                break;
            }
            
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE {$user_table} SET password = ? WHERE {$email_column} = ?");
            
            if ($stmt->execute([$hashed_password, $email])) {
                // Clean up used OTPs
                $stmt = $conn->prepare("DELETE FROM {$otp_table} WHERE email = ?");
                $stmt->execute([$email]);
                
                $response['success'] = true;
                $response['message'] = 'Password reset successfully.';
            } else {
                $response['message'] = 'Failed to reset password. Please try again.';
            }
            break;
            
        default:
            $response['message'] = 'Invalid action.';
    }
}

echo json_encode($response);
?>
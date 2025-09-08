<?php
session_start();
include '../config/config.php';

$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Find supplier with this token
        $stmt = $conn->prepare("SELECT id, contact_person_email, company_name FROM suppliers WHERE verification_token = ? AND email_verified = 0");
        $stmt->execute([$token]);
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($supplier) {
            // Verify the supplier
            $stmt = $conn->prepare("UPDATE suppliers SET email_verified = 1, verification_token = NULL, status = 'active' WHERE id = ?");
            $stmt->execute([$supplier['id']]);
            
            $message = 'Email verified successfully! You can now login to your account.';
            $success = true;
            
            // Send welcome email
            $subject = "Welcome to Purewood ERP - Account Activated";
            $email_message = "Dear " . $supplier['company_name'] . ",\n\nYour supplier account has been successfully verified and activated.\n\nYou can now login to your account at: " . BASE_URL . "supplieradmin/login.php\n\nThank you for joining our supplier network.\n\nBest regards,\nPurewood Team";
            mail($supplier['contact_person_email'], $subject, $email_message);
            
        } else {
            $message = 'Invalid or expired verification link.';
        }
    } catch (PDOException $e) {
        $message = 'Verification failed. Please try again.';
    }
} else {
    $message = 'Invalid verification link.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Purewood ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .verification-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .error-icon {
            color: #dc3545;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <?php if ($success): ?>
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="text-success mb-3">Verification Successful!</h2>
            <?php else: ?>
                <i class="fas fa-times-circle error-icon"></i>
                <h2 class="text-danger mb-3">Verification Failed</h2>
            <?php endif; ?>
            
            <p class="mb-4"><?php echo htmlspecialchars($message); ?></p>
            
            <?php if ($success): ?>
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login Now
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back to Registration
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
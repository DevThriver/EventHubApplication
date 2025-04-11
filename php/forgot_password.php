<?php
session_start();

require 'userdb.php';        // Database connection
require 'mail_config.php';   // Email configuration

// Check if the request method is POST (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim the email input
    $email = trim($_POST['email']);
    
    // Validate email field is not empty
    if (empty($email)) {
        $_SESSION['error'] = "Please enter your email address.";
        header("Location: ../html/index.html#pills-login");
        exit();
    }
    
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);  
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If user with this email exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();  // Get user data
        
        // Generate a secure random token for password reset
        $reset_token = bin2hex(random_bytes(32));  // 64-character hex token
        // Set token expiry to 1 hour from now
        $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store the token and expiry in database
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $update->bind_param("ssi", $reset_token, $reset_expiry, $user['id']);
        
        if ($update->execute()) {
            // Create reset link with token
            $reset_link = "http://localhost/EventHub/php/reset_password.php?token=$reset_token";
            
            // Email content
            $email_subject = "Password Reset Request";
            $email_body = "Click to reset your password: <a href='$reset_link'>$reset_link</a>";
            
            // Attempt to send email using mail configuration
            if (sendEmail($email, $email_subject, $email_body)) {
                // Success message
                $_SESSION['message'] = "Reset link sent! Please check your email.";
                header("Location: ../php/message.php");
                exit();
            } else {
                // Email sending failed
                $_SESSION['error'] = "Failed to send email.";
                header("Location: ../html/index.html#pills-login");
                exit();
            }
        }
    }
    
    // Generic response 
    $_SESSION['message'] = "If an account exists, a reset link was sent.";
    header("Location: ../php/message.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom container styling */
        .container { max-width: 500px; margin-top: 50px; }
    </style>
</head>
<body>
    <!-- Main container -->
    <div class="container">
        <!-- Card component for the form -->
        <div class="card">
            <!-- Card header with title -->
            <div class="card-header bg-primary text-white">
                <h4>Reset Password</h4>
            </div>
            <!-- Card body with form content -->
            <div class="card-body">
                <!-- Display error message if exists -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); // Clear the error after displaying ?>
                <?php endif; ?>
                
                <!-- Password reset form -->
                <form method="POST">
                    <!-- Email input field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                    <!-- Back to login link -->
                    <div class="mt-3 text-center">
                        <a href="../html/index.html" class="text-decoration-none">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
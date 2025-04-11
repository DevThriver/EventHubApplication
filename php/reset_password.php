<?php
session_start();
require 'userdb.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle the reset link
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token exists and isn't expired
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $token);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $_SESSION['reset_token'] = $token;
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reset Password</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                .container { max-width: 500px; margin-top: 50px; }
                #passwordFeedback, #confirmFeedback { display: none; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4>Reset Your Password</h4>
                    </div>
                    <div class="card-body">
                        <div id="message" class="alert alert-danger d-none"></div>
                        <form id="resetForm">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div id="passwordFeedback" class="invalid-feedback"></div>
                                <small class="text-muted">Must be at least 8 characters with uppercase and number</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div id="confirmFeedback" class="invalid-feedback"></div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('resetForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Clear previous messages
                    document.getElementById('message').classList.add('d-none');
                    document.getElementById('password').classList.remove('is-invalid');
                    document.getElementById('confirm_password').classList.remove('is-invalid');
                    
                    // Get form values
                    const password = document.getElementById('password').value;
                    const confirm_password = document.getElementById('confirm_password').value;
                    const token = '<?= $_GET['token'] ?>';
                    
                    // Client-side validation
                    let valid = true;
                    
                    if (password.length < 8) {
                        showError('password', 'Password must be at least 8 characters');
                        valid = false;
                    }
                    
                    if (!/[A-Z]/.test(password)) {
                        showError('password', 'Must contain at least one uppercase letter');
                        valid = false;
                    }
                    
                    if (!/[0-9]/.test(password)) {
                        showError('password', 'Must contain at least one number');
                        valid = false;
                    }
                    
                    if (password !== confirm_password) {
                        showError('confirm_password', 'Passwords do not match');
                        valid = false;
                    }
                    
                    if (!valid) return;
                    
                    // Submit via AJAX
                    fetch('reset_password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `password=${encodeURIComponent(password)}&confirm_password=${encodeURIComponent(confirm_password)}&token=${token}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            showMessage(data.error);
                        }
                    })
                    .catch(error => {
                        showMessage('An error occurred. Please try again.');
                        console.error('Error:', error);
                    });
                });
                
                function showError(fieldId, message) {
                    const field = document.getElementById(fieldId);
                    const feedback = document.getElementById(fieldId + 'Feedback');
                    field.classList.add('is-invalid');
                    feedback.textContent = message;
                    feedback.style.display = 'block';
                }
                
                function showMessage(message) {
                    const msgElement = document.getElementById('message');
                    msgElement.textContent = message;
                    msgElement.classList.remove('d-none');
                }
            </script>
        </body>
        </html>
        <?php
        exit();
    }
}

// Handle the password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Get raw POST data
        $postData = file_get_contents('php://input');
        parse_str($postData, $params);
        
        $token = $params['token'] ?? '';
        $password = trim($params['password'] ?? '');
        $confirm_password = trim($params['confirm_password'] ?? '');
        
        // Server-side validation
        $errors = [];
        
        if (empty($token) || !isset($_SESSION['reset_token']) || $token !== $_SESSION['reset_token']) {
            throw new Exception("Invalid reset token");
        }
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }
        
        // Verify token again
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows !== 1) {
            throw new Exception("Invalid or expired reset token");
        }
        
        $user = $result->fetch_assoc();
        
        // Update password and clear token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $update->bind_param("si", $hashed_password, $user['id']);
        
        if (!$update->execute()) {
            throw new Exception("Failed to update password");
        }
        
        // Clear the session token
        unset($_SESSION['reset_token']);
        
        echo json_encode([
            'success' => true,
            'redirect' => '../php/message.php?reset_success=1'
        ]);
        exit();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit();
    }
}

// If nothing matches, redirect to index
header("Location: ../html/index.html");
exit();
?>
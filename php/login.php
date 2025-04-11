<?php
session_start();
require 'userdb.php';

// Redirect authenticated users
if (isset($_SESSION['user_id'])) {
    header("Location: ../php/welcome.php");
    exit();
}

// Debug settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Enforces login attempt limits and lockout periods for brute-force protection.
 */
function checkLoginAttempts($conn, $email) {
    $max_attempts = 5;
    $lockout_time = 15 * 60; // 15 minutes in seconds
    
    $stmt = $conn->prepare("SELECT attempts, last_attempt FROM userlogin_attempts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['attempts'] >= $max_attempts) {
            $time_since_last = time() - strtotime($row['last_attempt']);
            if ($time_since_last < $lockout_time) {
                $remaining_time = ceil(($lockout_time - $time_since_last) / 60);
                die("Too many failed attempts. Try again in $remaining_time minutes.");
            } else {
                // Reset attempts after lockout expires
                $reset = $conn->prepare("UPDATE userlogin_attempts SET attempts = 0 WHERE email = ?");
                $reset->bind_param("s", $email);
                $reset->execute();
            }
        }
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        die("Please fill in all fields.");
    }

    checkLoginAttempts($conn, $email);

    // Fetch user data
    $stmt = $conn->prepare("SELECT id, username, password, user_type, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password, $user_type, $is_verified);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            if (!$is_verified) {
                // Track unverified login attempts
                $update = $conn->prepare("INSERT INTO userlogin_attempts (email, attempts, last_attempt) 
                                         VALUES (?, 1, NOW()) 
                                         ON DUPLICATE KEY UPDATE 
                                         attempts = attempts + 1, last_attempt = NOW()");
                $update->bind_param("s", $email);
                $update->execute();
                die("Your email is not verified. Please check your inbox.");
            }

            // Clear attempts on successful login
            $reset = $conn->prepare("DELETE FROM userlogin_attempts WHERE email = ?");
            $reset->bind_param("s", $email);
            $reset->execute();

            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['last_login'] = time();

            // Check if user needs to set preferences (first login or no preferences)
            $loginCheck = $conn->prepare("SELECT u.first_login, COUNT(uc.category_id) as pref_count 
                                        FROM users u
                                        LEFT JOIN user_categories uc ON u.id = uc.user_id
                                        WHERE u.id = ?");
            $loginCheck->bind_param("i", $id);
            $loginCheck->execute();
            $loginResult = $loginCheck->get_result();
            $loginData = $loginResult->fetch_assoc();

            // Update first_login flag if needed
            if ($loginData['first_login'] == 1) {
                $updateStmt = $conn->prepare("UPDATE users SET first_login = 0 WHERE id = ?");
                $updateStmt->bind_param("i", $id);
                $updateStmt->execute();
            }

            // Redirect based on preferences
            if ($loginData['pref_count'] == 0) {
                header("Location: ../php/interestedin.php");
            } else {
                header("Location: ../php/welcome.php");
            }
            exit();

        } else {
            // Log failed attempt
            $update = $conn->prepare("INSERT INTO userlogin_attempts (email, attempts, last_attempt) 
                                    VALUES (?, 1, NOW()) 
                                    ON DUPLICATE KEY UPDATE 
                                    attempts = attempts + 1, last_attempt = NOW()");
            $update->bind_param("s", $email);
            $update->execute();
            die("Invalid email or password.");
        }
    } else {
        // Log attempt for non-existent email
        $update = $conn->prepare("INSERT INTO userlogin_attempts (email, attempts, last_attempt) 
                                VALUES (?, 1, NOW()) 
                                ON DUPLICATE KEY UPDATE 
                                attempts = attempts + 1, last_attempt = NOW()");
        $update->bind_param("s", $email);
        $update->execute();
        die("Invalid email or password.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 500px; margin-top: 50px; }
        .alert { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Login</h2>
        
        <!-- Display verification/reset success messages -->
        <?php if (isset($_GET['verification_success'])) : ?>
            <div class="alert alert-success">
                Email verified successfully! You can now login.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset_success'])) : ?>
            <div class="alert alert-success">
                Password reset successfully! You can now login with your new password.
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Login</button>
            
            <div class="mt-3 text-center">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            
            <div class="mt-3 text-center">
                Don't have an account? <a href="../html/index.html#pills-register">Register here</a>
            </div>
        </form>
    </div>
</body>
</html>
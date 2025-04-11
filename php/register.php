<?php
session_start();
require 'userdb.php';
require 'mail_config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../php/welcome.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $department = trim($_POST['department']);

    // Validate email domain
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@(stu\.ncu\.edu\.jm|ncu\.edu\.jm)$/', $email)) {
        $_SESSION['error'] = "Invalid email domain. Please use your NCU email!";
        header("Location: ../html/index.html#pills-register");
        exit();
    }

    // Validate password match
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match. Please check and try again.";
        header("Location: ../html/index.html#pills-register");
        exit();
    }

    // Validate password strength
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and include uppercase, lowercase, and numbers.";
        header("Location: ../html/index.html#pills-register");
        exit();
    }

    // Determine user type based on email domain
    $isStudent = strpos($email, '@stu.ncu.edu.jm') !== false;
    $user_type = $isStudent ? 'student' : 'host';

    // Validate required fields based on user type
    if ($isStudent) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        if (empty($first_name) || empty($last_name)) {
            $_SESSION['error'] = "Please fill in all fields.";
            header("Location: ../html/index.html#pills-register");
            exit();
        }
    } else {
        $host_name = trim($_POST['host_name']);
        if (empty($host_name)) {
            $_SESSION['error'] = "Please fill in all fields.";
            header("Location: ../html/index.html#pills-register");
            exit();
        }
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: ../html/index.html#pills-register");
        exit();
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username already taken.";
        header("Location: ../html/index.html#pills-register");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate verification token and expiry
    $verification_token = bin2hex(random_bytes(32));
    $verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, verification_token, verification_expiry) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $hashed_password, $user_type, $verification_token, $verification_expiry);

        if (!$stmt->execute()) {
            throw new Exception("Error registering user.");
        }

        $user_id = $conn->insert_id;

        // Insert into appropriate table (students or hosts)
        if ($isStudent) {
            $stmt = $conn->prepare("INSERT INTO students (id, first_name, last_name, department) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $first_name, $last_name, $department);
        } else {
            $stmt = $conn->prepare("INSERT INTO hosts (id, host_name, department) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $host_name, $department);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error completing registration.");
        }

        // Send verification email
        $verification_link = "http://localhost/EventHub/php/verify_email.php?token=$verification_token";
        
            $email_subject = "Verify Your Email Address";
            $email_body = "Click to verify: $verification_link\n\n"
                        . "Or visit: http://localhost:8025 to view this email in MailHog.\n"
                        . "This link expires in 24 hours.";
    
            if (!sendEmail($email, $email_subject, $email_body)) {
                throw new Exception("Registration completed but verification email could not be sent.");
            }
    
            // Commit transaction
            $conn->commit();

        // Set success message
        $_SESSION['message'] = "Registration successful! Check MailHog at http://localhost:8025 to view the verification email.";
        header("Location: ../php/message.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../html/index.html#pills-register");
        exit();
    }
} else {
    // Not a POST request
    header("Location: ../html/index.html");
    exit();
}
?>
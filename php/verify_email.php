<?php
session_start();
require 'userdb.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT id, verification_expiry FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $expiry);
        $stmt->fetch();
        
        if (strtotime($expiry) > time()) {
            // Token is valid and not expired
            $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_expiry = NULL WHERE id = ?");
            $update->bind_param("i", $user_id);
            
            if ($update->execute()) {
                $_SESSION['message'] = "Email verified successfully! You can now login.";
                header("Location: ../php/message.php");
                exit();
            } else {
                $_SESSION['message'] = "Error verifying email. Please try again.";
                header("Location: ../php/message.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Verification link has expired. Please register again.";
            header("Location: ../php/message.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Invalid verification link.";
        header("Location: ../php/message.php");
        exit();
    }
} else {
    header("Location: ../html/index.html");
    exit();
}
?>
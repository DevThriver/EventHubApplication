<?php
session_start();

if (isset($_GET['reset_success'])) {
    $_SESSION['message'] = "Your password has been reset successfully! You can now login with your new password.";
}

if (!isset($_SESSION['message'])) {
    header("Location: ../html/index.html");
    exit();
}

$message = $_SESSION['message'];
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Message</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <a href="../html/index.html" class="btn btn-primary">Return to Login</a>
    </div>
</body>
</html>
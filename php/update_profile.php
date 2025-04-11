<?php
session_start();
require '../php/userdb.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = $_POST['bio'] ?? '';
    $interests = $_POST['interests'] ?? '';

    // Handle file upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = basename($_FILES['profile_pic']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_path)) {
            $profile_pic = $file_name;
        } else {
            $profile_pic = 'default-profile.jpg';
        }
    } else {
        $profile_pic = 'default-profile.jpg';
    }

    // Insert or update profile data
    $stmt = $conn->prepare("
        INSERT INTO user_profile (user_id, profile_pic, bio, interests)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        profile_pic = VALUES(profile_pic),
        bio = VALUES(bio),
        interests = VALUES(interests)
    ");
    $stmt->bind_param("isss", $user_id, $profile_pic, $bio, $interests);
    $stmt->execute();

    header("Location: profile.php");
    exit();
}
?>
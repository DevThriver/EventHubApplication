<?php
session_start();
require 'userdb.php';

// Fetch all users (students and hosts) and their profile pictures
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.user_type, up.profile_pic 
    FROM users u
    LEFT JOIN user_profile up ON u.id = up.user_id
    WHERE u.id != ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    // Prepend the 'uploads/' directory to the profile_pic path
    if (!empty($row['profile_pic'])) {
        $row['profile_pic'] = '../uploads/' . $row['profile_pic'];
    } else {
        // Provide a default avatar if no profile picture is set
        $row['profile_pic'] = 'default-profile.jpg';
    }
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
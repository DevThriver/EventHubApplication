<?php
session_start();

require '../php/userdb.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = json_decode(file_get_contents('php://input'), true)['event_id'];

// Check if the user has already liked the event
$stmt = $conn->prepare("SELECT * FROM event_likes WHERE user_id = ? AND event_id = ?");
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unlike the event
    $stmt = $conn->prepare("DELETE FROM event_likes WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $action = 'unlike';
} else {
    // Like the event
    $stmt = $conn->prepare("INSERT INTO event_likes (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $event_id);
    $action = 'like';
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'action' => $action]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error toggling like']);
}
?>
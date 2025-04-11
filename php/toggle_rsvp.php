<?php
session_start();
require '../php/userdb.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$event_id = $data['event_id'];

// Check current RSVP status
$check = $conn->prepare("SELECT 1 FROM event_rsvps WHERE user_id = ? AND event_id = ?");
$check->bind_param("ii", $user_id, $event_id);
$check->execute();
$is_rsvped = $check->get_result()->num_rows > 0;

if ($is_rsvped) {
    // Cancel RSVP
    $stmt = $conn->prepare("DELETE FROM event_rsvps WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $action = 'cancel';
} else {
    // Add RSVP
    $stmt = $conn->prepare("INSERT INTO event_rsvps (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $event_id);
    $action = 'rsvp';
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'action' => $action,
        'new_state' => !$is_rsvped // true if now RSVPed, false if now canceled
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
}
?>
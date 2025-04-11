<?php
session_start();
require 'userdb.php';

$data = json_decode(file_get_contents('php://input'), true);

// Insert the message 
$stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $data['sender_id'], $data['receiver_id'], $data['message']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
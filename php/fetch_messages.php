<?php
// Start or resume the current session
session_start();

// Include the database connection file
require 'userdb.php';

// Enable detailed error reporting for debugging
error_reporting(E_ALL);        // Report all PHP errors
ini_set('display_errors', 1); 

// Retrieve sender and receiver IDs from URL query parameters
$senderId = $_GET['sender_id'];   
$receiverId = $_GET['receiver_id']; 

/*
 * Prepare SQL query to fetch messages between two users:
 * - Joins with users table to get sender's username
 * - Retrieves messages in both directions (A→B and B→A)
 * - Orders messages chronologically (oldest first)
 */
$stmt = $conn->prepare("
    SELECT 
        cm.*,                                        // All fields from chat_messages
        u.username AS sender_name                    // Sender's username from users table
    FROM chat_messages cm
    JOIN users u ON cm.sender_id = u.id              // Join to get sender details
    WHERE 
        (cm.sender_id = ? AND cm.receiver_id = ?)    // Messages from A to B
        OR 
        (cm.receiver_id = ? AND cm.sender_id = ?)    // Messages from B to A
    ORDER BY cm.created_at ASC                       // Sort by creation time (oldest first)
");

// Bind the user IDs as integers to the prepared statement
$stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);

// Execute the prepared query
$stmt->execute();

// Get the result set from the executed query
$result = $stmt->get_result();

// Initialize an empty array to store messages
$messages = [];

// Loop through each row in the result set
while ($row = $result->fetch_assoc()) {
    // Add each message (as an associative array) to the messages array
    $messages[] = $row;
}

// Set the HTTP response header to indicate JSON content
header('Content-Type: application/json');

// Output the messages array as a JSON string
echo json_encode($messages);
?>
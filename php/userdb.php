<?php
$host = 'localhost';
$dbname = 'event_hub';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* if ($conn) {
    echo "Database connected successfully!";
} */

?>

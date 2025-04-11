<?php
// Start or resume existing session to maintain user state
session_start();

// Include database connection file for user data
require 'userdb.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/index.html");
    exit(); // Ensure script stops execution after redirect
}

// Check if the form was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data from POST request
    $title = $_POST['title'];          // Event title
    $description = $_POST['description']; // Event description
    $event_type = $_POST['event_type'];  // Event category/type
    $start_date = $_POST['start_date'];  // Event start date and time
    $end_date = $_POST['end_date'];      // Event end date and time
    $location = $_POST['location'];      // Event location
    $created_by = $_SESSION['user_id'];  // ID of the user creating the event

    // Handle image upload if a file was submitted
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        
        // Create full path for the uploaded file
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);

        // Move the uploaded file from temporary location to upload directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $uploadFile; // Store the file path if upload succeeds
        } else {
            die("Error: Unable to upload image."); // Terminate if upload fails
        }
    } else {
        $image = null; // Set to null if no image was uploaded
    }

    // Prepare SQL statement to insert new event into database
    $stmt = $conn->prepare("INSERT INTO events (title, description, event_type, start_date, end_date, location, created_by, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters to the prepared statement
    $stmt->bind_param("ssssssis", $title, $description, $event_type, $start_date, $end_date, $location, $created_by, $image);

    // Execute the prepared statement
    if ($stmt->execute()) {
        // Redirect to calendar page if event creation succeeds
        header("Location: ../php/calendar.php");
        exit();
    } else {
        // Terminate with error message if execution fails
        die("Error: Unable to create event.");
    }
}
?>
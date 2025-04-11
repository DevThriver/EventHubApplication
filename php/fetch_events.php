<?php
require 'userdb.php';

// Get start and end date parameters from the URL query string
// Uses null coalescing operator (??) to provide default null values if parameters are not set
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

// SQL query to fetch event data
$query = "SELECT id, title, start_date as start, end_date as end, event_type, description, image FROM events";

// Add date range filtering if both start and end parameters are provided
if ($start && $end) {
    $query .= " WHERE start_date >= ? AND end_date <= ?";
}

// Always order events by start date (ascending)
$query .= " ORDER BY start_date ASC";

// Prepare the SQL statement to prevent SQL injection
$stmt = $conn->prepare($query);

// Bind parameters if date range filtering is being used
if ($start && $end) {
    // Bind the start and end date parameters as strings ('s' type)
    $stmt->bind_param("ss", $start, $end);
}

// Execute the prepared statement
$stmt->execute();

// Get the result set from the executed statement
$result = $stmt->get_result();

// Initialize an empty array to store events
$events = [];

// Fetch each row from the result set and add it to the events array
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Set the response content type to JSON
header('Content-Type: application/json');

// Output the events array as JSON
echo json_encode($events);
?>
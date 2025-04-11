<?php
// Start or resume existing session to maintain user state
session_start();

// Include database connection file for user data
require 'userdb.php';

// Verify that the current user is a host (event organizer)
if ($_SESSION['user_type'] !== 'host') {
    die("Access denied."); // Terminate script if user is not a host
}

// Get event ID from URL query parameters
$event_id = $_GET['event_id'];

// Prepare and execute query to fetch basic event details
$event_query = "SELECT title FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("i", $event_id); // Bind event ID parameter (integer)
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc(); // Get event data as associative array

// Prepare and execute query to fetch attendees for this event
$attendees_query = "
    SELECT u.username 
    FROM users u
    JOIN event_rsvps r ON u.id = r.user_id
    WHERE r.event_id = ?
";
$attendees_stmt = $conn->prepare($attendees_query);
$attendees_stmt->bind_param("i", $event_id); // Bind event ID parameter (integer)
$attendees_stmt->execute();
$attendees_result = $attendees_stmt->get_result();
$attendees = $attendees_result->fetch_all(MYSQLI_ASSOC); // Get all attendees as associative array
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <!-- Page title -->
    <title>Event Attendees</title>
    
    <!-- External CSS libraries -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome for additional icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS files -->
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/event_attendees.css">
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <!-- Brand/logo link to homepage -->
            <a class="navbar-brand" href="../php/welcome.php">EventHub</a>
            
            <!-- Mobile menu toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Home/Feed link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/welcome.php">
                            <i class="bi bi-house-door me-1"></i>Feed
                        </a>
                    </li>
                    
                    <!-- Events link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/calendar.php">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                    
                    <!-- Chats link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/chats.php">
                            <i class="bi bi-chat-left-text me-1"></i>Chats
                        </a>
                    </li>
                    
                    <!-- Recommendations link -->
                    <li class="nav-item">
                        <a class="nav-link " href="../php/recommendation.php">
                            <i class="bi bi-stars me-1"></i>Recommended
                        </a>
                    </li>
                    
                    <!-- Profile link (active page) -->
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/profile.php">
                            <i class="bi bi-person me-1"></i>Profile
                        </a>
                    </li>
                    
                    <!-- Logout button -->
                    <li class="nav-item ms-2">
                        <a href="../php/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<!-- Main Content Area -->
<main>
    <div class="maincontainer mt-5">
        <!-- Event title header (escaped for security) -->
        <h1 class="mb-4">Attendees for <?php echo htmlspecialchars($event['title']); ?></h1>
        
        <!-- Attendees list -->
        <ul class="list-group">
            <?php if (!empty($attendees)): ?>
                <!-- Loop through each attendee and display their username -->
                <?php foreach ($attendees as $attendee): ?>
                    <li class="list-group-item bg-light"><?php echo htmlspecialchars($attendee['username']); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Display message if no attendees found -->
                <li class="list-group-item">No attendees found.</li>
            <?php endif; ?>
        </ul>
        
        <!-- Back button to return to My Events page -->
        <a href="my_events.php" class="btn btn-primary mt-4">Back to My Events</a>
    </div>
</main>

   <!-- Footer Section -->
   <footer class="footer bg-dark mt-auto">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <!-- Copyright notice -->
                    <p>&copy; 2025 Event Hub. All rights reserved.</p>
                    
                    <!-- Footer links -->
                    <ul class="footer-links list-inline">
                        <li class="list-inline-item"><a href="../php/welcome.php">Feed</a></li>
                        <li class="list-inline-item"><a href="../php/calendar.php">Events</a></li>
                        <li class="list-inline-item"><a href="../php/chats.php">Chats</a></li>
                        <li class="list-inline-item"><a href="../php/recommendation.php">Recommended</a></li>
                        <li class="list-inline-item"><a href="../php/profile.php">Profile</a></li>
                        <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                        <li class="list-inline-item"><a href="#">Terms of Service</a></li>
                        <li class="list-inline-item"><a href="#">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

</body>
   <!-- JavaScript Libraries -->
   <!-- Bootstrap Bundle with Popper -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>
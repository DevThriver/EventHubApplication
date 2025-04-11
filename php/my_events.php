<?php
session_start();
require 'userdb.php';

// Retrieve current user's ID and type
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

/*
 * - Students see events they registered for
 * - Hosts see events they created with attendee counts
 */
if ($user_type === 'student') {
    // Query for students: get all events they RSVP'd to
    $query = "
        SELECT e.id, e.title, e.description, e.image, e.start_date, e.end_date, e.location 
        FROM events e
        JOIN event_rsvps r ON e.id = r.event_id
        WHERE r.user_id = ?
        ORDER BY e.start_date DESC
    ";
} elseif ($user_type === 'host') {
    // Query for hosts: get created events with attendee counts
    $query = "
        SELECT e.id, e.title, e.description, e.image, e.start_date, e.end_date, e.location,
               COUNT(r.user_id) AS attendee_count
        FROM events e
        LEFT JOIN event_rsvps r ON e.id = r.event_id
        WHERE e.created_by = ?
        GROUP BY e.id
        ORDER BY e.start_date DESC
    ";
} else {
    // Block invalid user types
    die("Invalid user type.");
}

// Prepare the SQL statement
$stmt = $conn->prepare($query);
// Bind the user ID parameter
$stmt->bind_param("i", $user_id);
// Execute the query
$stmt->execute();
// Get result set
$result = $stmt->get_result();
// Fetch all results as associative array
$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Standard HTML meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events | EventHub</title>
    
    <!-- External CSS libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS files -->
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/my_events.css">
</head>
<body>
   <!-- Navigation Bar -->
   <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../php/welcome.php">EventHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../php/welcome.php">
                            <i class="bi bi-house-door me-1"></i>Feed
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/calendar.php">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/chats.php">
                            <i class="bi bi-chat-left-text me-1"></i>Chats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/recommendation.php">
                            <i class="bi bi-stars me-1"></i>Recommended
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="../php/profile.php">
                            <i class="bi bi-person me-1"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="../php/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Primary content container -->
    <main class="container mt-5 pt-4 mb-5">
        <!-- Page header with search box -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-5 fw-bold mt-5">My Events</h1>
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="Search events...">
                <i class="bi bi-search search-icon"></i>
            </div>
        </div>

        <!-- Events grid layout -->
        <div class="row g-4" id="event-grid">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <!-- Individual event card -->
                    <div class="col-lg-4 col-md-6">
                        <div class="event-card card h-100 shadow-sm">
                            <!-- Event image with proper sanitization -->
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                 class="card-img-top event-image" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>">
                            
                            <!-- Event details -->
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($event['description']); ?></p>
                                
                                <!-- Event metadata section -->
                                <div class="event-meta mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock text-primary me-2"></i>
                                        <span>
                                            <?php
                                            $start_date = date("M j, Y g:i a", strtotime($event['start_date']));
                                            $end_date = date("g:i a", strtotime($event['end_date']));
                                            echo "$start_date - $end_date";
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Host-specific controls -->
                                <?php if ($user_type === 'host'): ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo $event['attendee_count']; ?> attending
                                        </span>
                                        <a href="event_attendees.php?event_id=<?php echo $event['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary mt-2">
                                            View Attendees
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty state when no events exist -->
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">No events found</h3>
                        <p class="text-muted">You haven't <?php echo $user_type === 'student' ? 'RSVPed to' : 'created'; ?> any events yet</p>
                        <?php if ($user_type === 'host'): ?>
                            <a href="../php/create_event_form.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-1"></i> Create Your First Event
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back navigation button -->
        <div class="col-md-12">
            <div class="d-flex justify-content-between mt-4">
                <a href="../php/calendar.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Calendar
                </a>
            </div>
        </div>
    </main>

     <!-- Footer -->
     <footer class="footer bg-dark mt-auto">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <p>&copy; 2025 Event Hub. All rights reserved.</p>
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

    <!-- JavaScript libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript for search functionality -->
    <script>
    // Add event listener for search input
    document.getElementById('searchInput').addEventListener('input', function() {
        // Get current search term in lowercase
        const searchTerm = this.value.toLowerCase();
        // Select all event card containers
        const eventCards = document.querySelectorAll('.col-lg-4.col-md-6');
        
        // Process each event card
        eventCards.forEach(function(card) {
            // Get event title text
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            // Get date element if exists
            const dateElement = card.querySelector('.event-meta span');
            // Get date text or empty string
            const date = dateElement ? dateElement.textContent.toLowerCase() : '';
            
            // Show/hide based on search match
            if (title.includes(searchTerm) || date.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
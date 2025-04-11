<?php
// Start or resume existing session
session_start();

// Include database connection file for user data
require 'userdb.php';

// Get the user's role from session (assumes user_type is set during login)
$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

// Fetch all events the current user has RSVP'd to
$user_rsvps = [];
$rsvp_query = "SELECT event_id FROM event_rsvps WHERE user_id = ?";
$stmt = $conn->prepare($rsvp_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rsvp_result = $stmt->get_result();
while ($row = $rsvp_result->fetch_assoc()) {
    $user_rsvps[$row['event_id']] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic meta tags for character set and responsive viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Page title -->
    <title>Event Calendar | EventHub</title>
    
    <!-- External CSS libraries -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome for additional icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- FullCalendar CSS for calendar styling -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    
    <!-- Custom CSS files -->
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/calendar.css">
    <link rel="stylesheet" href="/css/footer.css">
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
                    
                    <!-- Events link (active page) -->
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/calendar.php">
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
                        <a class="nav-link" href="../php/recommendation.php">
                            <i class="bi bi-stars me-1"></i>Recommended
                        </a>
                    </li>
                    
                    <!-- Profile link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/profile.php">
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
    <main class="container mt-5 pt-4 mb-5">
        <!-- Calendar header section -->
        <div class="calendar-header mb-4">
            <h2 class="display-6 fw-bold mb-3">Event Calendar</h2>
            
            <!-- Action buttons and month navigation -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <!-- Button group for event actions -->
                <div class="btn-group">
                    <?php if ($user_type === 'host'): ?>
                        <!-- Create Event button (only visible to hosts) -->
                        <a class="btn btn-danger" href="../php/create_event_form.php">
                            <i class="fas fa-plus me-1"></i>Create Event
                        </a>
                    <?php endif; ?>
                    
                    <!-- My Events button -->
                    <a class="btn btn-primary ms-2" href="../php/my_events.php">
                        <i class="fas fa-calendar-days me-1"></i>My Events
                    </a>
                </div>
                
                <!-- Month navigation controls -->
                <div class="d-flex align-items-center">
                    <!-- Previous month button -->
                    <button id="prevMonth" class="btn btn-outline-primary me-2">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <!-- Current month display -->
                    <h4 id="currentMonth" class="mb-0 fw-bold text-center" style="min-width: 200px;"></h4>
                    
                    <!-- Next month button -->
                    <button id="nextMonth" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid container for event cards -->
        <div id="event-grid" class="row g-4"></div>
    </main>

    <!-- Footer Section -->
    <footer class="footer mt-3">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <!-- Copyright notice -->
                    <p>&copy; 2025 Event Hub. All rights reserved.</p>
                    
                    <!-- Footer links -->
                    <ul class="footer-links">
                        <li><a href="../php/welcome.php">Feed</a></li>
                        <li><a href="../php/calendar.php">Events</a></li>
                        <li><a href="../php/chats.php">Chats</a></li>
                        <li><a href="../php/recommendation.php">Recommended</a></li>
                        <li><a href="../php/profile.php">Profile</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <!-- FullCalendar library -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- First JavaScript block for event filtering functionality -->
    <script>
        // Wait for DOM to be fully loaded before executing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to DOM elements
            const eventGrid = document.getElementById('event-grid');
            const eventTypeFilter = document.getElementById('eventTypeFilter');

            // Function to fetch and display events with optional filtering
            function fetchEvents(eventType = 'all') {
                // Fetch events from server
                fetch('../php/fetch_events.php')
                    .then(response => response.json())
                    .then(events => {
                        // Clear existing events
                        eventGrid.innerHTML = '';

                        // Filter events based on type if specified
                        const filteredEvents = eventType === 'all'
                            ? events
                            : events.filter(event => event.event_type === eventType);

                        // Create and append event cards for each filtered event
                        filteredEvents.forEach(event => {
                            const eventCard = document.createElement('div');
                            eventCard.className = 'event-card';
                            const eventDate = new Date(event.start).getDate();
                            const hasRsvped = <?php echo json_encode($user_rsvps); ?>[event.id] ? true : false;
                            
                            // Event card HTML template
                            eventCard.innerHTML = `
                                <div class="event-image-container">
                                    <img src="${event.image}" alt="${event.title}" class="event-image">
                                    <div class="event-date">${eventDate}</div>
                                </div>
                                <div class="event-details">
                                    <h5 class="event-title">${event.title}</h5>
                                    <p class="event-description" title="${event.description}">${event.description}</p>
                                    <p class="event-time">${new Date(event.start).toLocaleString()} - ${new Date(event.end).toLocaleString()}</p>
                                    <div class="event-actions">
                                        <button class="btn-rsvp ${hasRsvped ? 'active' : ''}" 
                                                data-event-id="${event.id}">
                                            <i class="${hasRsvped ? 'fas' : 'far'} fa-registered"></i>
                                            ${hasRsvped ? 'Cancel RSVP' : 'RSVP'}
                                        </button>
                                    </div>
                                </div>
                            `;
                            eventGrid.appendChild(eventCard);
                        });
                    })
                    .catch(error => console.error('Error fetching events:', error));
            }

            // Initial fetch (show all events)
            fetchEvents();

            // Add event listener for filter dropdown changes
            if (eventTypeFilter) {
                eventTypeFilter.addEventListener('change', function() {
                    const selectedEventType = this.value;
                    fetchEvents(selectedEventType);
                });
            }
        });
    </script>

    <!-- Second JavaScript block for month navigation functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to DOM elements
            const eventGrid = document.getElementById('event-grid');
            const currentMonthElement = document.getElementById('currentMonth');
            const prevMonthButton = document.getElementById('prevMonth');
            const nextMonthButton = document.getElementById('nextMonth');

            // Initialize current date, month, and year
            let currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();

            // Function to update the month display
            function updateMonthDisplay() {
                const monthNames = ["January", "February", "March", "April", "May", "June", 
                                   "July", "August", "September", "October", "November", "December"];
                currentMonthElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
            }

            // Function to fetch events for the current month
            function fetchEvents() {
                // Calculate start and end of current month
                const startOfMonth = new Date(currentYear, currentMonth, 1).toISOString().split('T')[0];
                const endOfMonth = new Date(currentYear, currentMonth + 1, 0).toISOString().split('T')[0];

                // Fetch events for the current month range
                fetch(`../php/fetch_events.php?start=${startOfMonth}&end=${endOfMonth}`)
                    .then(response => response.json())
                    .then(events => {
                        // Clear existing events
                        eventGrid.innerHTML = '';
                        
                        // Create and append event cards
                        events.forEach(event => {
                            const eventCard = document.createElement('div');
                            eventCard.className = 'event-card';
                            const eventDate = new Date(event.start).getDate();
                            const hasRsvped = <?php echo json_encode($user_rsvps); ?>[event.id] ? true : false;
                            eventCard.innerHTML = `
                                <div class="event-image-container">
                                    <img src="${event.image}" alt="${event.title}" class="event-image">
                                    <div class="event-date">${eventDate}</div>
                                </div>
                                <div class="event-details">
                                    <h5 class="event-title">${event.title}</h5>
                                    <p class="event-description">${event.description}</p>
                                    <p class="event-time">${new Date(event.start).toLocaleString()} - ${new Date(event.end).toLocaleString()}</p>
                                    <div class="event-actions">
                                        <button class="btn-rsvp ${hasRsvped ? 'active' : ''}" 
                                                data-event-id="${event.id}">
                                            <i class="${hasRsvped ? 'fas' : 'far'} fa-registered"></i>
                                            ${hasRsvped ? 'Cancel RSVP' : 'RSVP'}
                                        </button>
                                    </div>
                                </div>
                            `;
                            eventGrid.appendChild(eventCard);
                        });
                    })
                    .catch(error => console.error('Error fetching events:', error));
            }

            // Initial setup
            updateMonthDisplay();
            fetchEvents();

            // Event listener for previous month button
            prevMonthButton.addEventListener('click', function() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                updateMonthDisplay();
                fetchEvents();
            });

            // Event listener for next month button
            nextMonthButton.addEventListener('click', function() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                updateMonthDisplay();
                fetchEvents();
            });
        });
    </script>

    <!-- RSVP Functionality Script -->
    <script>
        // Add RSVP functionality to event cards
        document.addEventListener('DOMContentLoaded', function() {
            // Event delegation for RSVP buttons (since cards are dynamically loaded)
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-rsvp') || e.target.closest('.btn-rsvp')) {
                    const button = e.target.classList.contains('btn-rsvp') ? e.target : e.target.closest('.btn-rsvp');
                    const eventId = button.getAttribute('data-event-id');
                    const icon = button.querySelector('i');
                    
                    toggleRSVP(button, eventId, icon);
                }
            });

            // Function to handle RSVP toggle
            async function toggleRSVP(button, eventId, icon) {
                try {
                    // Show loading state
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    
                    // Send request to server
                    const response = await fetch('../php/toggle_rsvp.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ event_id: eventId })
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to update RSVP');
                    }
                    
                    // Update button state
                    if (icon) {
                        icon.className = data.action === 'rsvp' ? 'fas fa-registered' : 'far fa-registered';
                    }
                    button.textContent = data.action === 'rsvp' ? 'Cancel RSVP' : 'RSVP';
                    data.action === 'rsvp' ? button.classList.add('active') : button.classList.remove('active');
                    
                } catch (error) {
                    console.error('RSVP Error:', error);
                    // Only show alert for non-network errors
                    if (!error.message.includes('NetworkError')) {
                        alert('Error: ' + error.message);
                    }
                } finally {
                    button.disabled = false;
                }
            }
        });
    </script>

    <!-- Additional Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
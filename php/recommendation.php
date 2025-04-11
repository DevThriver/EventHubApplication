<?php
session_start();
require 'userdb.php'; // Include your database connection file

// Fetch event_likes data
$event_likes_query = "SELECT user_id, event_id FROM event_likes";
$event_likes_result = $conn->query($event_likes_query);

// Fetch event_rsvps data
$event_rsvps_query = "SELECT user_id, event_id FROM event_rsvps";
$event_rsvps_result = $conn->query($event_rsvps_query);

// Combine likes and RSVPs into a single array
$user_interactions = [];
while ($row = $event_likes_result->fetch_assoc()) {
    $user_interactions[] = $row;
}
while ($row = $event_rsvps_result->fetch_assoc()) {
    $user_interactions[] = $row;
}

// Create a user-event matrix
$user_event_matrix = [];
foreach ($user_interactions as $interaction) {
    $user_id = $interaction['user_id'];
    $event_id = $interaction['event_id'];
    if (!isset($user_event_matrix[$user_id])) {
        $user_event_matrix[$user_id] = [];
    }
    $user_event_matrix[$user_id][$event_id] = 1;
}

// Function to compute Jaccard Similarity
function jaccard_similarity($user1, $user2, $user_event_matrix) {
    $events_user1 = array_keys($user_event_matrix[$user1]);
    $events_user2 = array_keys($user_event_matrix[$user2]);
    $intersection = array_intersect($events_user1, $events_user2);
    $union = array_unique(array_merge($events_user1, $events_user2));
    return count($intersection) / count($union);
}

// Compute user similarity matrix
$user_similarity = [];
foreach ($user_event_matrix as $user1 => $events1) {
    foreach ($user_event_matrix as $user2 => $events2) {
        if ($user1 != $user2) {
            $user_similarity[$user1][$user2] = jaccard_similarity($user1, $user2, $user_event_matrix);
        }
    }
}

// Function to recommend events
function recommend_events($user_id, $user_similarity, $user_event_matrix, $num_recommendations = 5) {
    // Check if the user has any interactions
    if (!isset($user_event_matrix[$user_id]) || empty($user_event_matrix[$user_id])) {
        return []; // Return an empty array if the user has no interactions
    }

    // Find similar users
    $similar_users = $user_similarity[$user_id];
    arsort($similar_users); // Sort by similarity score
    $similar_users = array_slice($similar_users, 0, 10, true); // Top 10 similar users

    // Get events interacted with by similar users
    $recommended_events = [];
    foreach ($similar_users as $similar_user => $score) {
        $events = array_keys($user_event_matrix[$similar_user]);
        foreach ($events as $event) {
            if (!isset($user_event_matrix[$user_id][$event])) {
                if (!isset($recommended_events[$event])) {
                    $recommended_events[$event] = 0;
                }
                $recommended_events[$event] += $score; // Weight by similarity score
            }
        }
    }

    // Sort events by score
    arsort($recommended_events);

    // Return top N recommendations
    return array_slice(array_keys($recommended_events), 0, $num_recommendations);
}

// Fetch recommendations for the logged-in user
$user_id = $_SESSION['user_id'];
$recommended_events = recommend_events($user_id, $user_similarity, $user_event_matrix);

// Fetch event details from the database and check RSVP status
$event_details = [];
if (!empty($recommended_events)) {
    // Get all events user has RSVP'd to
    $rsvp_query = "SELECT event_id FROM event_rsvps WHERE user_id = ?";
    $stmt = $conn->prepare($rsvp_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $rsvp_result = $stmt->get_result();
    $user_rsvps = [];
    while ($row = $rsvp_result->fetch_assoc()) {
        $user_rsvps[$row['event_id']] = true;
    }

    // Fetch event details and check RSVP status
    $event_ids = implode(",", $recommended_events);
    $event_query = "SELECT e.id, e.title, e.description, e.image, e.location, e.start_date, e.end_date, 
                   COUNT(r.user_id) AS rsvp_count
                   FROM events e
                   LEFT JOIN event_rsvps r ON e.id = r.event_id
                   WHERE e.id IN ($event_ids)
                   GROUP BY e.id";
    $event_result = $conn->query($event_query);
    
    while ($event = $event_result->fetch_assoc()) {
        $event['has_rsvped'] = isset($user_rsvps[$event['id']]);
        $event_details[] = $event;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Events | EventHub</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/recommendation.css">
    <link rel="stylesheet" href="/css/footer.css">
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
                        <a class="nav-link" href="../php/calendar.php">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/chats.php">
                            <i class="bi bi-chat-left-text me-1"></i>Chats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/recommendation.php">
                            <i class="bi bi-stars me-1"></i>Recommended
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/profile.php">
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

    <!-- Main Content -->
    <main class="container mt-5 pt-4">
        <div class="recommendation-header">
            <h1>Recommended For You</h1>
            <p class="text-muted mt-2">Events tailored to your interests</p>
        </div>

        <div class="events-grid">
            <?php if (!empty($event_details)): ?>
                <?php foreach ($event_details as $event): ?>
                    <div class="event-card">
                        <div class="event-image-container">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                 class="event-image">
                            <div class="event-badge">Recommended</div>
                        </div>
                        <div class="event-card-body">
                            <h3 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                            
                            <div class="event-meta">
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <div class="event-time">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>
                                        <?php
                                        $start_date = date("M j, Y | g:i a", strtotime($event['start_date']));
                                        $end_date = date("g:i a", strtotime($event['end_date']));
                                        echo "$start_date - $end_date";
                                        ?>
                                    </span>
                                </div>
                                <div class="event-rsvp-count mt-3 mb-3">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $event['rsvp_count']; ?> attending</span>
                                </div>
                            </div>
                            
                            <div class="event-actions">
                                <button class="btn-rsvp <?php echo $event['has_rsvped'] ? 'active' : ''; ?>" 
                                        data-event-id="<?php echo $event['id']; ?>">
                                    <i class="<?php echo $event['has_rsvped'] ? 'fas' : 'far'; ?> fa-registered"></i>
                                    <?php echo $event['has_rsvped'] ? 'Cancel RSVP' : 'RSVP'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <h3>No recommendations yet</h3>
                    <p>Start liking or RSVPing to events to get personalized recommendations</p>
                    <a href="../php/calendar.php" class="btn btn-primary">
                        <i class="fas fa-calendar me-1"></i> Browse Events
                    </a>
                </div>
            <?php endif; ?>
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

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-rsvp').forEach(button => {
        button.addEventListener('click', async function() {
            const eventId = this.getAttribute('data-event-id');
            const icon = this.querySelector('i');
            const rsvpCountElement = this.closest('.event-card')?.querySelector('.event-rsvp-count span');
            
            try {
                // Show loading state
                const originalHTML = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
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
                this.textContent = data.action === 'rsvp' ? 'Cancel RSVP' : 'RSVP';
                data.action === 'rsvp' ? this.classList.add('active') : this.classList.remove('active');
                
                // Update count if element exists
                if (rsvpCountElement) {
                    const currentCount = parseInt(rsvpCountElement.textContent) || 0;
                    rsvpCountElement.textContent = (currentCount + (data.action === 'rsvp' ? 1 : -1)) + ' attending';
                }
                
            } catch (error) {
                console.error('RSVP Error:', error);
                // Restore original button state
                this.innerHTML = originalHTML;
                // Only show alert for non-network errors
                if (!error.message.includes('NetworkError')) {
                    alert('Error: ' + error.message);
                }
            } finally {
                this.disabled = false;
            }
        });
    });
});
</script>
</body>
</html>
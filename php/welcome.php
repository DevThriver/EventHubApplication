<?php
session_start();
require '../php/userdb.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's current interests
$interests_stmt = $conn->prepare("SELECT category_id FROM user_categories WHERE user_id = ?");
$interests_stmt->bind_param("i", $user_id);
$interests_stmt->execute();
$interests_result = $interests_stmt->get_result();
$user_interests = array_column($interests_result->fetch_all(MYSQLI_ASSOC), 'category_id');

// Fetch all available categories
$categories_stmt = $conn->query("SELECT id, name FROM event_categories");
$all_categories = $categories_stmt->fetch_all(MYSQLI_ASSOC);

// Fetch events based on user interests
$stmt = $conn->prepare("
    SELECT 
        e.id, 
        e.title, 
        e.description, 
        e.start_date,
        e.image AS event_image, 
        e.location,
        e.created_by, 
        e.created_at, 
        u.username,
        up.profile_pic AS user_profile_pic,
        COUNT(DISTINCT el.user_id) AS like_count,
        COUNT(DISTINCT er.user_id) AS rsvp_count,
        MAX(CASE WHEN el.user_id = ? THEN 1 ELSE 0 END) AS has_liked,
        MAX(CASE WHEN er.user_id = ? THEN 1 ELSE 0 END) AS has_rsvped
    FROM events e
    JOIN users u ON e.created_by = u.id
    LEFT JOIN user_profile up ON u.id = up.user_id
    JOIN event_subcategories es ON e.event_type = es.id
    JOIN user_categories uc ON es.category_id = uc.category_id
    LEFT JOIN event_likes el ON e.id = el.event_id
    LEFT JOIN event_rsvps er ON e.id = er.event_id
    WHERE uc.user_id = ?
    GROUP BY e.id
    ORDER BY e.created_at DESC
");

$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Feed | EventHub</title>
    <link rel="icon" type="image/x-icon" href="/images/eventhub_logo_.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/event_feed.css">
    <link rel="stylesheet" href="/css/footer.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../php/welcome.php">EventHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/welcome.php">
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
                        <a class="nav-link" href="../php/recommendation.php">
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

    <main class="container mt-2 pt-4">
        <div class="welcome-banner text-center py-4 mb-4 rounded-3 position-relative">
            <button class="btn btn-outline-primary update-interests-btn" 
                    data-bs-toggle="modal" 
                    data-bs-target="#interestsModal">
                <i class="fas fa-edit me-1"></i> Update Interests
            </button>
            <h2 class="display-6 fw-bold">
                Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </h2>
            <p class="lead">Discover the latest events happening around campus</p>
        </div>

        <div class="row justify-content-center">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-lg-8 mb-4">
                        <div class="post-card card shadow-sm h-100">
                            <div class="card-header bg-white d-flex align-items-center">
                                <img src="../uploads/<?php echo htmlspecialchars($event['user_profile_pic']); ?>" 
                                     alt="Profile Picture" 
                                     class="profile-img rounded-circle me-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($event['username']); ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('M j, Y \a\t g:i A', strtotime($event['created_at'])); ?>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                            </div>

                            <img src="../uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                 alt="Event Image" 
                                 class="post-image card-img-top">

                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <div>
                                        <div class="interaction-buttons">
                                            <button class="btn-like btn btn-sm <?php echo $event['has_liked'] ? 'active' : ''; ?>" 
                                                    data-event-id="<?php echo $event['id']; ?>">
                                                <i class="<?php echo $event['has_liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
                                                <span class="count ms-1"><?php echo $event['like_count']; ?></span>
                                                <span class="text ms-1">likes</span>
                                            </button>

                                            <button class="btn-rsvp btn btn-sm <?php echo $event['has_rsvped'] ? 'active' : ''; ?>" 
                                                    data-event-id="<?php echo $event['id']; ?>">
                                                <i class="<?php echo $event['has_rsvped'] ? 'fas' : 'far'; ?> fa-registered"></i>
                                                <span class="count ms-1"><?php echo $event['rsvp_count']; ?></span>
                                                <span class="text ms-1">RSVPs</span>
                                            </button>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-share me-1"></i>Share
                                    </button>
                                </div>

                                <h4 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                
                                <div class="event-details mt-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        <span><?php echo date('l, F j, Y \a\t g:i A', strtotime($event['start_date'])); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-white">
                                <a href="#" class="btn btn-link text-decoration-none">
                                    <i class="bi bi-info-circle me-1"></i>Learn more
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state p-5 rounded-3 bg-light">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">No events found</h3>
                        <p class="text-muted">Check back later, update interests or create your own event!</p>
                        <a href="../php/create_event_form.php" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i>Create Event
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Interests Update Modal -->
    <div class="modal fade interests-modal" id="interestsModal" tabindex="-1" aria-labelledby="interestsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="interestsModalLabel">Update Your Interests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">Select the categories you're interested in to personalize your event feed:</p>
                    
                    <div class="row">
                        <?php foreach ($all_categories as $category): ?>
                            <div class="col-md-6 mb-3">
                                <div class="interest-item d-flex align-items-center <?php echo in_array($category['id'], $user_interests) ? 'selected' : ''; ?>"
                                     onclick="toggleInterest(this, <?php echo $category['id']; ?>)">
                                    <input type="checkbox" 
                                           class="interest-checkbox" 
                                           id="cat-<?php echo $category['id']; ?>"
                                           <?php echo in_array($category['id'], $user_interests) ? 'checked' : ''; ?>>
                                    <i class="fas fa-<?php 
                                        switch($category['id']) {
                                            case 1: echo 'graduation-cap'; break;
                                            case 2: echo 'pray'; break;
                                            case 3: echo 'running'; break;
                                            case 4: echo 'users'; break;
                                            case 5: echo 'hands-helping'; break;
                                            case 6: echo 'heartbeat'; break;
                                            case 7: echo 'laptop-code'; break;
                                            case 8: echo 'chalkboard-teacher'; break;
                                            case 9: echo 'gamepad'; break;
                                            default: echo 'star';
                                        }
                                    ?> interest-icon"></i>
                                    <label for="cat-<?php echo $category['id']; ?>" class="mb-0">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveInterests()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Animation for post cards
    document.addEventListener("DOMContentLoaded", () => {
        const postCards = document.querySelectorAll('.post-card');
        postCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('visible');
            }, index * 200);
        });

        // Like button functionality
        document.querySelectorAll('.btn-like').forEach(button => {
            button.addEventListener('click', () => {
                const eventId = button.getAttribute('data-event-id');
                toggleLike(eventId, button);
            });
        });

        // RSVP button functionality
        document.querySelectorAll('.btn-rsvp').forEach(button => {
            button.addEventListener('click', () => {
                const eventId = button.getAttribute('data-event-id');
                toggleRSVP(eventId, button);
            });
        });

        // Toggle interest selection visually
        function toggleInterest(element, categoryId) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('selected', checkbox.checked);
        }

        // Save interests to database
        window.saveInterests = function() {
            const checkboxes = document.querySelectorAll('.interest-checkbox:checked');
            const selectedInterests = Array.from(checkboxes).map(cb => {
                return parseInt(cb.id.replace('cat-', ''));
            });

            fetch('../php/update_interests.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    interests: selectedInterests
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('interestsModal'));
                    modal.hide();
                    
                    // Show success message
                    alert('Your interests have been updated successfully!');
                    
                    // Refresh the page to show updated feed
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update interests'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating your interests');
            });
        };
    });

    // Like/Unlike an event
    async function toggleLike(eventId, button) {
        const response = await fetch('../php/toggle_like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ event_id: eventId }),
        });
        const data = await response.json();
        if (data.success) {
            const icon = button.querySelector('i');
            const countSpan = button.querySelector('span.count');
            
            button.classList.toggle('active');
            
            if (data.action === 'like') {
                icon.classList.replace('far', 'fas');
                countSpan.textContent = parseInt(countSpan.textContent) + 1;
            } else {
                icon.classList.replace('fas', 'far');
                countSpan.textContent = parseInt(countSpan.textContent) - 1;
            }
        }
    }

    // RSVP/Un-RSVP to an event
    async function toggleRSVP(eventId, button) {
        const response = await fetch('../php/toggle_rsvp.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ event_id: eventId }),
        });
        const data = await response.json();
        if (data.success) {
            const icon = button.querySelector('i');
            const countSpan = button.querySelector('span.count');
            
            button.classList.toggle('active');
            
            if (data.action === 'rsvp') {
                icon.classList.replace('far', 'fas');
                countSpan.textContent = parseInt(countSpan.textContent) + 1;
            } else {
                icon.classList.replace('fas', 'far');
                countSpan.textContent = parseInt(countSpan.textContent) - 1;
            }
        }
    }
</script>
</body>
<footer class="footer bg-dark fixed-bottom" style="position: static;">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <p>&copy; 2025 Event Hub. All rights reserved.</p>
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
</html>
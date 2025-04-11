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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic meta tags for character set and responsive viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Page title -->
    <title>Create Event | EventHub</title>
    
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
    <link rel="stylesheet" href="/css/create_event_form.css">
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
    <main class="container mt-5 pt-4">
        <!-- Form container with centered content -->
        <div class="form-container">
            <!-- Form header with title and description -->
            <div class="form-header text-center mb-4">
                <h2 class="fw-bold">Create New Event</h2>
                <p class="text-muted">Fill out the form below to create your event</p>
            </div>
            
            <!-- Event creation form with POST method and file upload capability -->
            <form action="../php/create_event.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Event Title Field -->
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="title" id="title" class="form-control" placeholder="Event Title" required>
                            <label for="title">Event Title</label>
                        </div>
                    </div>
                    
                    <!-- Event Description Field -->
                    <div class="col-md-12">
                        <div class="form-floating">
                            <textarea name="description" id="description" class="form-control" placeholder="Event Description" style="height: 120px" required></textarea>
                            <label for="description">Event Description</label>
                        </div>
                    </div>
                    
                    <!-- Event Type Dropdown -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="event_type" class="form-label">Event Type</label>
                            <select name="event_type" id="event_type" class="form-select" required>
                                <!-- Default disabled option -->
                                <option value="" disabled selected>Select an option</option>
                                
                                <!-- Academics & Career Options -->
                                <optgroup label="Academics & Career">
                                    <option value=1>Scholarships</option>
                                    <option value=2>Internships</option>
                                    <option value=3>Research Opportunities</option>
                                    <option value=4>Study Abroad Programs</option>
                                    <option value=5>Career Fairs</option>
                                    <option value=6>Skill Development Workshops</option>
                                    <option value=7>Other</option>
                                </optgroup>

                                <!-- Faith & Spirituality Options -->
                                <optgroup label="Faith & Spirituality">
                                    <option value=8>Campus Ministries</option>
                                    <option value=9>Bible Study Groups</option>
                                    <option value=10>Prayer Meetings</option>
                                    <option value=11>Faith-Based Retreats</option>
                                    <option value=12>Spiritual Counseling</option>
                                    <option value=13>Other</option>
                                </optgroup>

                                <!-- Sports & Fitness Options -->
                                <optgroup label="Sports & Fitness">
                                    <option value=14>Intramural Sports</option>
                                    <option value=15>Intercollegiate Sports</option>
                                    <option value=16>Fitness Classes</option>
                                    <option value=17>Gym & Training Programs</option>
                                    <option value=18>Outdoor Adventures</option>
                                    <option value=19>Other</option>
                                </optgroup>

                                <!-- Social & Culture Options -->
                                <optgroup label="Social & Culture">
                                    <option value=20>Student Clubs & Societies</option>
                                    <option value=21>Cultural Festivals</option>
                                    <option value=22>Socials</option>
                                    <option value=23>Open Mic & Talent Shows</option>
                                    <option value=24>Other</option>
                                </optgroup>

                                <!-- Community Service & Volunteering Options -->
                                <optgroup label="Community Service & Volunteering">
                                    <option value=25>Charity Drives</option>
                                    <option value=26>Outreach Programs</option>
                                    <option value=27>Environmental Initiatives</option>
                                    <option value=28>Leadership & Service Projects</option>
                                    <option value=29>Other</option>
                                </optgroup>

                                <!-- Health & Wellness Options -->
                                <optgroup label="Health & Wellness">
                                    <option value=30>Mental Health Sessions</option>
                                    <option value=31>Stress Management Sessions</option>
                                    <option value=32>Nutrition & Healthy Living</option>
                                    <option value=33>Peer Support Groups</option>
                                    <option value=34>Other</option>
                                </optgroup>

                                <!-- Technology & Innovation Options -->
                                <optgroup label="Technology & Innovation">
                                    <option value=35>Hackathons & Coding Competitions</option>
                                    <option value=36>CHIPS Club</option>
                                    <option value=37>Startup Incubators</option>
                                    <option value=38>Tech Talks & Webinars</option>
                                    <option value=39>Other</option>
                                </optgroup>

                                <!-- Workshops & Seminars Options -->
                                <optgroup label="Workshops & Seminars">
                                    <option value=40>Career Development</option>
                                    <option value=41>Academic Enrichment</option>
                                    <option value=42>Technology & Innovation</option>
                                    <option value=43>Health & Wellness</option>
                                    <option value=44>Entrepreneurship & Business</option>
                                    <option value=45>Leadership & Personal Growth</option>
                                    <option value=46>Other</option>
                                </optgroup>

                                <!-- Entertainment & Recreation Options -->
                                <optgroup label="Entertainment & Recreation">
                                    <option value=47>Movie Nights</option>
                                    <option value=48>Concerts & Live Performances</option>
                                    <option value=49>Games or Tournaments</option>
                                    <option value=50>Social</option>
                                    <option value=51>Other</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Date & Time Fields -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" name="start_date" id="start_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date" class="form-label">End Date & Time</label>
                            <input type="datetime-local" name="end_date" id="end_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <!-- Location Field -->
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="location" id="location" class="form-control" placeholder="Location" required>
                            <label for="location">Location</label>
                        </div>
                    </div>
                    
                    <!-- Image Upload Field -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="image" class="form-label">Event Image</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended size: 1200x630 pixels</small>
                        </div>
                    </div>
                    
                    <!-- Form Action Buttons -->
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between mt-4">
                            <!-- Back to Calendar button -->
                            <a href="../php/calendar.php" class="btn btn-outline-secondary calendarbtn">
                                <i class="fas fa-arrow-left me-1"></i> Back to Calendar
                            </a>
                            
                            <!-- Submit button for form -->
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i> Create Event
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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

    <!-- JavaScript Libraries -->
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
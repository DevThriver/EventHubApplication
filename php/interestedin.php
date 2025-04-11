<?php
session_start();
require '../php/userdb.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/index.html");
    exit();
}

// Check if user already has preferences
$check = $conn->prepare("SELECT COUNT(*) as pref_count FROM user_categories WHERE user_id = ?");
$check->bind_param("i", $_SESSION['user_id']);
$check->execute();
$result = $check->get_result();
$data = $result->fetch_assoc();

// Redirect to welcome page if preferences exist
if ($data['pref_count'] > 0) {
    header("Location: ../php/welcome.php");
    exit();
}

// Handle GET request (clear any existing preferences)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];
    $clearStmt = $conn->prepare("DELETE FROM user_categories WHERE user_id = ?");
    $clearStmt->bind_param("i", $user_id);
    $clearStmt->execute();
}

// Handle POST request (save preferences)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Process selected interests
    if (isset($_POST['interests'])) {
        $preferences = json_encode($_POST['interests']);
        
        $stmt = $conn->prepare("INSERT INTO user_categories (user_id, category_id) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $preferences);
        
        if ($stmt->execute()) {
            header("Location: ../php/welcome.php");
            exit();
        } else {
            die("Error saving preferences");
        }
    }
    
    // Handle skip action
    if (isset($_POST['skip'])) {
        header("Location: ../php/welcome.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/interestedin.css">
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <a href="../php/logout.php" class="btn red-button">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="mainContainer">
        <h2 class="text-center mb-1 fw-bold">Select Your Interests</h2>
        <form method="POST" action="">
        <div id="categoryCarousel" class="carousel slide" data-bs-ride="false">
    <div class="carousel-inner">

    <!-- Category 1: Academics & Career -->
    <div class="carousel-item active">
            <img src="../images/academicsaandcareerimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Academics & Career</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(1)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(1)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 2: Faith & Spiritual Growth -->
        <div class="carousel-item">
            <img src="../images/faithandgrowthimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Faith & Spirituality</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(2)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(2)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 3: Sports & Fitness -->
        <div class="carousel-item">
            <img src="../images/sportsandfitnessimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Sports & Fitness</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(3)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(3)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 4: Social and Culture -->
        <div class="carousel-item">
            <img src="../images/socialandcultureimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Social and Culture</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(4)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(4)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 5: Community Service & Volunteering -->
        <div class="carousel-item">
            <img src="../images/communityserviceimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Community Service & Volunteering</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(5)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(5)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 6: Health & Wellness -->
        <div class="carousel-item">
            <img src="../images/healthandwellnessimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Health & Wellness</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(6)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(6)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 7: Technology & Innovation -->
        <div class="carousel-item">
            <img src="../images/technologyandinnovationimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Technology & Innovation</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(7)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(7)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 8: Workshops & Seminars -->
        <div class="carousel-item">
            <img src="../images/workshopandseminarimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Workshops & Seminars</h5>
                <p>Are you interested?</p>
                <button type="button" class="btn btn-success" onclick="toggleSelection(8)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(8)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>

        <!-- Category 9: Entertainment & Recreation -->
        <div class="carousel-item">
            <img src="../images/entertainmentimg.jpg" class="d-block w-100">
            <div class="carousel-caption">
                <h5>Entertainment & Recreation</h5>
                <p>Are you interested?</p>
                <button type="button submit" class="btn btn-success" onclick="toggleSelection(9)"><i class="fas fa-check"></i> Yes</button>
                <button type="button" class="btn btn-danger" onclick="toggleDeselection(9)"><i class="fas fa-times"></i> No</button>
            </div>
        </div>
    </div>

    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#categoryCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#categoryCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
</div>

<!-- Hidden input to store selected interests -->
<input type="hidden" name="interests" id="interestsInput">

<!-- Submit and Skip Buttons -->
<div class="text-center mt-4">
    <button type="button" class="btn btn-secondary" onclick="skipSelection()">Skip</button>
</div>
</div>
</div>


<script>     
    function toggleSelection(category_id) {
    console.log("Adding category:", category_id); // Debugging
    fetch('../php/save_interests.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'add', category_id: category_id }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Server response:", data); // Debugging
        if (data.success) {
            console.log('Category added successfully');
            moveToNextSlide(); // Move to the next slide after successful addition
        } else if (data.message === 'Category already exists') {
            console.log('Category already exists, skipping to next slide');
            moveToNextSlide(); // Skip to the next slide if the category is already saved
        } else {
            console.error('Error adding category:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function toggleDeselection(category_id) {
    console.log("Removing category:", category_id); // Debugging
    fetch('../php/save_interests.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'remove', category_id: category_id }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Server response:", data); // Debugging
        if (data.success) {
            console.log('Category removed successfully');
            moveToNextSlide(); // Move to the next slide after successful removal
        } else {
            console.error('Error removing category:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function moveToNextSlide() {
    const carousel = new bootstrap.Carousel(document.getElementById('categoryCarousel'));
    const totalSlides = document.querySelectorAll(".carousel-item").length;
    const currentSlideIndex = Array.from(document.querySelectorAll(".carousel-item")).findIndex(item => item.classList.contains('active'));

    // If it's the last slide, redirect to welcome.php
    if (currentSlideIndex === totalSlides - 1) {
        window.location.href = "../php/welcome.php";
    } else {
        carousel.next(); // Move to the next slide
    }
}

function skipSelection() {
    window.location.href = "../php/welcome.php";
}
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
<!-- Footer -->
<footer class="footer bg-dark mt-5 fixed-bottom" style="position: fixed;">
        <div class="row">
            <div class="col text-center">
                <p>&copy; 2025 Event Hub. All rights reserved.</p>
                <ul class="footer-links">
                    <li><a href="../php/welcome.php">Feed</a></li>
                    <li><a href="../php/calendar.php">Events</a></li>
                    <li><a href="../php/chats.php">Chats</a></li>
                    <li><a href="#">Recommended</a></li>
                    <li><a href="../php/profile.php">Profile</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
    </div>
</footer>
</html>
<?php
session_start();
require '../php/userdb.php'; // Include your database connection file

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data from users table
$user_query = $conn->prepare("SELECT username, user_type FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$user_type = $user['user_type'];
$department = '';

// Fetch department based on user type
if ($user_type === 'host') {
    $host_query = $conn->prepare("SELECT department FROM hosts WHERE id = ?");
    $host_query->bind_param("i", $user_id);
    $host_query->execute();
    $host_result = $host_query->get_result();
    $host = $host_result->fetch_assoc();
    $department = $host['department'] ?? 'No department assigned';
} elseif ($user_type === 'student') {
    $student_query = $conn->prepare("SELECT department FROM students WHERE id = ?");
    $student_query->bind_param("i", $user_id);
    $student_query->execute();
    $student_result = $student_query->get_result();
    $student = $student_result->fetch_assoc();
    $department = $student['department'] ?? 'No department assigned';
} else {
    $department = 'No department assigned';
}

// Fetch profile data from user_profile table
$profile_query = $conn->prepare("SELECT profile_pic, bio, interests FROM user_profile WHERE user_id = ?");
$profile_query->bind_param("i", $user_id);
$profile_query->execute();
$profile_result = $profile_query->get_result();
$profile = $profile_result->fetch_assoc();

// If no profile exists, initialize default values
if (!$profile) {
    $profile = [
        'profile_pic' => 'default-profile.jpg',
        'bio' => '',
        'interests' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/profile.css">
    <link rel="stylesheet" href="/css/navbar.css">
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
                        <a class="nav-link " href="../php/recommendation.php">
                            <i class="bi bi-stars me-1"></i>Recommended
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/profile.php">
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


<main>
<!-- Profile Page Content -->
<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-banner"></div>
        <div class="profile-info">
            <img src="../uploads/<?php echo htmlspecialchars($profile['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <p class="department"><?php echo htmlspecialchars($department); ?></p>
            <button class="edit-profile-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
        </div>
    </div>

    <!-- Bio Section -->
    <div class="bio-section">
        <h2>About</h2>
        <p><?php echo htmlspecialchars($profile['bio']); ?></p>
        <?php if (empty($profile['bio'])) {
            echo '<p>No information added yet.</p>';
        }
        ?>
    </div>

    <!-- Interests Section -->
    <div class="interests-section">
        <h2>Interests</h2>
        <div class="interests-list">
            <?php
            if (!empty($profile['interests'])) {
                $interests = explode(',', $profile['interests']);
                foreach ($interests as $interest) {
                    echo '<span class="interest-tag">' . htmlspecialchars(trim($interest)) . '</span>';
                }
            } else {
                echo '<p>No interests added yet.</p>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" action="../php/update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_pic" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="profile_pic"  name="profile_pic" style="color: rgb(10, 10, 113);">
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3" style="color: rgb(10, 10, 113);"><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="interests" class="form-label">Interests</label>
                        <input type="text" class="form-control" id="interests" style="color: rgb(10, 10, 113);" name="interests" value="<?php echo htmlspecialchars($profile['interests']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
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

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php
session_start();

require '../php/userdb.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$category_id = $data['category_id'];
$action = $data['action'];

// Verify that the user_id exists in the users table
$userStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Verify that the category_id exists in the event_categories table
$categoryStmt = $conn->prepare("SELECT id FROM event_categories WHERE id = ?");
$categoryStmt->bind_param("i", $category_id);
$categoryStmt->execute();
$categoryResult = $categoryStmt->get_result();

if ($categoryResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Category not found']);
    exit();
}

if ($action === 'add') {
    // Check if the category is already saved for the user
    $checkStmt = $conn->prepare("SELECT * FROM user_categories WHERE user_id = ? AND category_id = ?");
    $checkStmt->bind_param("ii", $user_id, $category_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Category already exists']);
    } else {
        // Insert the category into user_categories
        $stmt = $conn->prepare("INSERT INTO user_categories (user_id, category_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $category_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding category: ' . $stmt->error]);
        }
    }
} elseif ($action === 'remove') {
    // Remove the category from user_categories
    $stmt = $conn->prepare("DELETE FROM user_categories WHERE user_id = ? AND category_id = ?");
    $stmt->bind_param("ii", $user_id, $category_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing category: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require '../php/userdb.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data['interests'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$interests = $data['interests'];

try {
    $conn->begin_transaction();
    
    // Clear existing interests
    $delete = $conn->prepare("DELETE FROM user_categories WHERE user_id = ?");
    $delete->bind_param("i", $user_id);
    $delete->execute();
    
    // Insert new interests
    $insert = $conn->prepare("INSERT INTO user_categories (user_id, category_id) VALUES (?, ?)");
    foreach ($interests as $cat_id) {
        if (!is_numeric($cat_id)) continue;
        $insert->bind_param("ii", $user_id, $cat_id);
        $insert->execute();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Interests updated successfully',
        'updated' => count($interests)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
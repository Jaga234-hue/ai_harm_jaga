<?php
require_once 'dbconnect.php';

// Should return bool(true) if connection is alive
// Check if user is logged in via cookies
if (!isset($_COOKIE['username']) && !isset($_COOKIE['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

// Get logged-in user's ID
$loginUsername = isset($_COOKIE['username']) ? $_COOKIE['username'] : $_COOKIE['email'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $loginUsername, $loginUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}
$user = $result->fetch_assoc();
$loggedInUserId = $user['user_id'];

// Validate target user ID
if (!isset($_POST['target_user_id']) || !ctype_digit($_POST['target_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
    exit;
}
$targetUserId = $_POST['target_user_id'];

// Prevent self-request
 if ($loggedInUserId == $targetUserId) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot send request to yourself.']);
    exit;
} 

// Check for existing relationship
  $checkStmt = $conn->prepare("SELECT relationship_id FROM friend_relationships 
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
$checkStmt->bind_param("iiii", $loggedInUserId, $targetUserId, $targetUserId, $loggedInUserId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Request already exists.']);
    exit;
}  

// Insert new relationship
$insertStmt = $conn->prepare("INSERT INTO friend_relationships (user1_id, user2_id, action_user_id) 
    VALUES (?, ?, ?)");
$insertStmt->bind_param("iii", $loggedInUserId, $targetUserId, $loggedInUserId);

if ($insertStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Friend request sent.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}

$insertStmt->close();
$conn->close();
?>
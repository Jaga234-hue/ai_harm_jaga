<?php
require_once 'dbconnect.php';

header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Validate required data
if (!isset($_POST['text'])) {
    echo json_encode(['status' => 'error', 'message' => 'Message content is required.']);
    exit;
}

$message = trim($_POST['text']);

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
    exit;
}

// Check if user is logged in
if (!isset($_COOKIE['username']) && !isset($_COOKIE['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

// Check opponent_id
if (!isset($_COOKIE['opponent_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Recipient not specified.']);
    exit;
}
$opponent_id = $_COOKIE['opponent_id'];

// Get logged-in user's ID
$loginUsername = isset($_COOKIE['username']) ? $_COOKIE['username'] : $_COOKIE['email'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $loginUsername, $loginUsername);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}
$loggedInUserId = $user['user_id'];

// Insert message
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $loggedInUserId, $opponent_id, $message);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
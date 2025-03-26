<?php
require_once 'dbconnect.php';

if (!isset($loggedInUserId)) {
    echo '<div class="error">Not authenticated.</div>';
    exit;
}

if (!isset($_COOKIE['opponent_id'])) {
    echo '<div class="error">No conversation partner selected.</div>';
    exit;
}

$opponent_id = (int)$_COOKIE['opponent_id'];

// Fetch messages between logged-in user and opponent
$stmt = $conn->prepare("
    SELECT m.*, u.username as sender_name 
    FROM messages m 
    INNER JOIN users u ON m.sender_id = u.user_id 
    WHERE (m.sender_id = ? AND m.receiver_id = ?)
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
");
$stmt->bind_param("iiii", $loggedInUserId, $opponent_id, $opponent_id, $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="info">Start a conversation!</div>';
} else {
    while ($message = $result->fetch_assoc()) {
        $messageClass = ($message['sender_id'] == $loggedInUserId) 
                        ? 'sent-message' 
                        : 'received-message';
        $formattedTime = date('M j, H:i', strtotime($message['created_at']));
        
        echo '<div class="message ' . $messageClass . '">';
        echo   '<div class="message-header">';
        echo     '<span class="sender">' . htmlspecialchars($message['sender_name']) . '</span>';
        echo     '<span class="time">' . $formattedTime . '</span>';
        echo   '</div>';
        echo   '<div class="content">' . htmlspecialchars($message['content']) . '</div>';
        echo '</div>';
    }
}

$stmt->close();
$conn->close();
?>
<style>
.message {
    margin: 10px;
    padding: 8px 12px;
    border-radius: 15px;
    max-width: 70%;
}

.sent-message {
    background: #4CAF50;
    color: white;
    margin-left: auto;
}

.received-message {
    background: #e0e0e0;
    color: black;
    margin-right: auto;
}

.message-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.8em;
    margin-bottom: 4px;
}

.time {
    opacity: 0.7;
}
</style>
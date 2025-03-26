<?php
require 'dbconnect.php'; // Ensure database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['opponent_id'])) {
        $opponent_id = intval($_POST['opponent_id']);
        /* echo 'Opponent ID: ' . $opponent_id . */ '<br>'; // Debugging
    setcookie("opponent_id", $opponent_id, time() + (86400 * 30), "/");
        // Fetch user details
        $stmt = $conn->prepare("SELECT username, profile_pic_url FROM users WHERE user_id = ?");
        if (!$stmt) {
            echo 'Prepare failed: (' . $conn->errno . ') ' . $conn->error;
        }
        $stmt->bind_param("i", $opponent_id);
        if (!$stmt->execute()) {
            echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
        }
        $result = $stmt->get_result();
        /* echo 'Number of rows: ' . $result->num_rows . */ '<br>'; // Debugging

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo '<div class="opponent-details">';
            echo '<img src="' . htmlspecialchars($user['profile_pic_url']) . '" alt="Profile Picture" class="profile-pic">';
            echo '<h2>' . htmlspecialchars($user['username']) . '</h2>';
            echo '</div>';
        } else {
            echo '<p>User not found.</p>';
        }

        $stmt->close();
    } else {
        echo '<p>Invalid request.</p>';
    }
} else {
    echo '<p>Invalid method.</p>';
}
?>
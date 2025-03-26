<?php
require_once 'dbconnect.php';
$profilePic = $username = null;

if (isset($_POST['query'])) {
    $searchQuery = trim($_POST['query']);
    
    if (!ctype_digit($searchQuery)) {
        echo "<p class='error'>Invalid User ID format</p>";
        exit;
    }

    $stmt = $conn->prepare("SELECT 
        user_id, 
        username, 
        email, 
        profile_pic_url,
        created_at
    FROM users 
    WHERE user_id = ?");

    $stmt->bind_param("i", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="search-result-item">';
            
            // Profile picture
            $profilePic = !empty($row['profile_pic_url']) 
                ? htmlspecialchars($row['profile_pic_url']) 
                : 'images/default-profile.png';
            
            echo '<img src="' . $profilePic . '" alt="Profile Picture">';
            $username = htmlspecialchars($row['username']);
            
            // User info
            echo '<div class="user-info">';
            echo ' <h3>'. htmlspecialchars($row['username']). '</h3>' . '</p>';
            /* echo '<p><strong>Email:</strong> ' . htmlspecialchars($row['email']) . '</p>'; */
            echo '<p><strong>User ID:</strong> <span class="user-id">' . htmlspecialchars($row['user_id']) . '</span></p>';
           /*  echo '<p><strong>Joined:</strong> ' . date('M d, Y', strtotime($row['created_at'])) . '</p>'; */
            echo '</div>';
            
            // Action button
            echo '<button class="follow-button msgbtn" data-userid="' . htmlspecialchars($row['user_id']) . '">message</button>';
            echo '</div>';
            
        }
    } else {
        echo "<p class='no-results'>No users found with this ID</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
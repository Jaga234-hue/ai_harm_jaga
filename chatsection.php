<?php
<?php
$host = "34.174.116.174"; // Your Cloud SQL Public IP
$user = "root";           // Default MySQL root user
$pass = "yUQ|1za5DdzK=aiX";               // No password (if allowed)
$dbname = "ai-social-world"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// echo "Connected successfully";
?>

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

/* echo "Connected successfully";  */

if (!isset($_COOKIE['username']) && !isset($_COOKIE['email'])) {
    echo '<div class="error">Not logged in.</div>';
    exit;
}

$loginUsername = $_COOKIE['username'] ?? $_COOKIE['email'];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $loginUsername, $loginUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="error">User not found.</div>';
    exit;
}

$user = $result->fetch_assoc();
$loggedInUserId = $user['user_id'];

$stmt = $conn->prepare("
    SELECT 
        u.user_id,
        u.username,
        u.profile_pic_url
    FROM friend_relationships fr
    INNER JOIN users u
        ON u.user_id = CASE 
                        WHEN fr.user1_id = ? THEN fr.user2_id
                        ELSE fr.user1_id
                       END
    WHERE (fr.user1_id = ? OR fr.user2_id = ?)
      AND fr.status = 'pending'
");
$stmt->bind_param("iii", $loggedInUserId, $loggedInUserId, $loggedInUserId);
$stmt->execute();
$opponentsResult = $stmt->get_result();

if ($opponentsResult->num_rows > 0) {
    while ($opponent = $opponentsResult->fetch_assoc()) {
        $profileImage = !empty($opponent['profile_pic_url'])
            ? htmlspecialchars($opponent['profile_pic_url'])
            : 'default-profile.jpg';
        $username = htmlspecialchars($opponent['username']);
        echo <<<HTML
<style>
    .chat-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid black;
        background-color: #f5f5f5;
        transition: background-color 0.2s;
        cursor: pointer;
    }

    .chat-item:hover {
        background-color: #e0e0e0;
    }

    .chat-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 15px;
        object-fit: cover;
        border: 2px solid #ddd;
    }

    .chat-username {
        font-family: Arial, sans-serif;
        font-size: 16px;
        color: #333;
        font-weight: 500;
    }

    .no-chats {
        padding: 20px;
        color: #666;
        font-style: italic;
    }

    .error {
        color: #ff4444;
        padding: 10px;
    }
</style>
HTML;
echo <<<HTML
<div class="chat-item" data-user-id="{$opponent['user_id']}">
    <img src="$profileImage" alt="$username" class="chat-avatar">
    <span class="chat-username">$username</span>
</div>
HTML;

 
    }
} else {
    echo '<div class="no-chats">No chat connections found</div>';
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatItems = document.querySelectorAll('.chat-item');
    const opponentShow = document.getElementById('opponent-show');

    // Load last selected chat on page load
    const lastOpponentId = localStorage.getItem('lastOpponentId');
    if (lastOpponentId) {
        const savedChatItem = document.querySelector(`.chat-item[data-user-id="${lastOpponentId}"]`);
        if (savedChatItem) {
            // Remove active class from all items first
            chatItems.forEach(chat => chat.classList.remove('active'));
            savedChatItem.classList.add('active');
            
            // Fetch and display chat details
            let formData = new FormData();
            formData.append('opponent_id', lastOpponentId);
            fetch('get_chat_details.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                opponentShow.innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
        }
    }

    // Click handler for chat items
    chatItems.forEach(item => {
        item.addEventListener('click', function() {
            chatItems.forEach(chat => chat.classList.remove('active'));
            this.classList.add('active');

            const userId = this.dataset.userId;
            localStorage.setItem('lastOpponentId', userId); // Store ID

            let formData = new FormData();
            formData.append('opponent_id', userId);
            fetch('get_chat_details.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                opponentShow.innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>

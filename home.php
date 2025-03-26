<?php
require_once('dbconnect.php');
// Check if cookies are set before accessing them
// default

$user_id = 0000;
$username = 'username';
$email = 'email@gmail.com';

if (isset($_COOKIE["username"]) || isset($_COOKIE["email"])) {
    $username = isset($_COOKIE["username"]) ? $_COOKIE["username"] : null;
    $email = isset($_COOKIE["email"]) ? $_COOKIE["email"] : null;

    $query = "SELECT user_id, username, email, profile_pic_url, bio FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user["user_id"];
        $username = $user["username"];
        $email = $user["email"];
        $profile_plc_url = $user["profile_pic_url"];
        $bio = $user["bio"];
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Cookies not set";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Interface</title>
    <link rel="stylesheet" href="home.css">
</head>

<body>
    <div class="main-container">
        <div class="header">
            <div class="profile-icn" id="profileIcn" onclick="toggleProfile()">
                <div class="profile-pic"></div>
                <div class="profile-name"><?php echo $username ?></div>
            </div>
            <div class="profileDetails" id="profileDetails" style="display: none;">
                <div class="profilePic" id="profilePic">
                    <img src="<?php echo htmlspecialchars($profile_plc_url); ?>" alt="Profile Picture">
                </div>
                <div class="user-id" id="userId"><?php echo $user_id ?></div>
                <div class="profileName" id="profileName"><?php echo $username ?></div>
                <div class="profile-email" id="profileEmail"><?php echo $email ?></div>
                <div id="bioDisplay"><?php echo htmlspecialchars($bio ?? 'No bio yet'); ?></div>
                <div class="edit-profile" id="editProfile" style="cursor:pointer">✏️</div>
            </div>
            <div class="edit-bio" id="editBio" style="display: none;">
                <h2>Edit Profile</h2>
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <label for="user_name">Name</label>
                    <input type="text" name="user_name" value="<?php echo $username; ?>">
                    <label for="bio">Bio</label>
                    <input type="text" name="bio" value="<?php echo $bio; ?>">
                    <label for="profile_picture"> Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*">
                    <label for="email"> Email</label>
                    <input type="email" name="email" value="<?php echo $email; ?>">
                    <button type="submit"> Update</button>
                </form>
                <span class="close" id="close" style="cursor:pointer" title="Close"> ❌ </span>
            </div>

            <div class="search-container">
                <div class="search-bar">
                    <input type="text" id="searchInput"
                        placeholder="Search users by user_id"
                        onkeyup="searchUsers()">
                </div>
            </div>
            <div class="menu" onclick="toggleMenu()">
                <h1>☰</h1>
            </div>
        </div>
        <div class="body">
            <div class="left" id="leftPanel">
                <div class="chat-list" id="chatList"><?php include 'chatsection.php'; ?></div>
                <div class="mn" id="chats">
                    <h3>chats</h3>
                </div>
                <div class="mn" id="request">
                    <h3>Requests</h3>
                </div>
                <div class="mn" id="friends">
                    <h3>Friends</h3>
                </div>
                <div class="mn" id="settings">
                    <h3>Settings</h3>
                </div>
            </div>

            <div class="right">
                <div id="searchResults"></div>
                <div class="opponent-show" id="opponent-show">
                </div>
                <div class="chat-section" id="chatSection">
                    <?php include 'chat_message.php'; ?>
                    <div class="post-container"></div>
                </div>
                <div class="footer" id="footer">
                    <!-- <form action="send_message.php" method="POST">
                        <input type="text" placeholder="Type a message..." name="message" id="message">
                        <button type="submit" name="send" class="send" id="send">📨</button>
                    </form> -->

                    <div class="container">
                        <form id="analysisForm" >
                            <div class="textarea-container">
                                <div class="attachment-wrapper">
                                    <span class="attach-btn">
                                        <span>🖼</span>
                                        <span id="fileInfo" class="file-info"></span>
                                        <input
                                            id="fileInput"
                                            type="file"
                                            name="file"
                                            accept="image/*, video/*"
                                            style="display: none" />
                                    </span>
                                </div>
                                <textarea name="text" placeholder="Enter text to check..."></textarea>
                            </div>
                            <button type="submit">Check</button>
                        </form>
                        <div class="warning-container" id="warningContainer"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="home.js"></script>
</body>

</html>
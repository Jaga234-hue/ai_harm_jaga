<?php
require_once('dbconnect.php');

// Get form data
$user_name = $_POST["user_name"];
$bio = $_POST["bio"];
$email = $_POST["email"];
$upload_dir = "profile/";

// Check if the profile/ directory exists, if not, create it
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fetch user_id based on email
$query = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit();
}

$user_id = $user['user_id'];
$profile_pic_path = ""; // Default empty

// Check if file was uploaded
if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
    $file_name = basename($_FILES["profile_picture"]["name"]);
    $target_file = $upload_dir . time() . "_" . $file_name; // Unique filename

    // Move uploaded file to the profile directory
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $profile_pic_path = $target_file; // Store relative path
    } else {
        echo "Failed to upload file.";
        exit();
    }
}

// Update query based on whether a new profile picture was uploaded
if (!empty($profile_pic_path)) {
    $query = "UPDATE users SET username = ?, bio = ?, profile_pic_url = ?, email = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $user_name, $bio, $profile_pic_path, $email, $user_id);
} else {
    $query = "UPDATE users SET username = ?, bio = ?, email = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $user_name, $bio, $email, $user_id);
}

$stmt->execute();

// Check if update was successful
if ($stmt->affected_rows > 0) {
    echo "Profile updated successfully!";
    header("Location: home.php");
} else {
    echo "No changes were made or update failed.";
}

$stmt->close();
$conn->close();
?>
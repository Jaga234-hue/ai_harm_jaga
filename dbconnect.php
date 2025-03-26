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

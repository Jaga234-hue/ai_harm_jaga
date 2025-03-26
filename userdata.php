<?php
session_start();
require 'dbconnect.php'; // Include database connection

// Function to generate a unique user ID
function generateUserId($conn)
{
    do {
        $user_id = rand(10000000, 99999999); // Generate an 8-digit random ID
        $query = "SELECT user_id FROM users WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
    } while (mysqli_stmt_num_rows($stmt) > 0);

    mysqli_stmt_close($stmt);
    return $user_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
        // Signup Handling
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        setcookie("username", $username, time() + (86400 * 30), "/");
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $check_email = "SELECT email FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            echo "<script>alert('Email already registered. Try logging in.'); window.location.href='index.html';</script>";
            exit;
        }
        mysqli_stmt_close($stmt);

        // Generate a unique user_id
        $user_id = generateUserId($conn);
        // Insert new user into the database using a prepared statement
        $insert_query = "INSERT INTO users (user_id, username, email, password_hash) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $username, $email, $hashed_password);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Signup successful! Please login.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Signup failed. Try again later.'); window.location.href='index.php';</script>";
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['loginUsername']) && isset($_POST['loginPassword'])) {
        // Login Handling
        $loginUsername = trim($_POST['loginUsername']);
        $loginPassword = trim($_POST['loginPassword']);
        setcookie("username", $loginUsername, time() + (86400 * 30), "/");
        setcookie("email", $loginUsername, time() + (86400 * 30), "/");
        // Check if user exists
        $query = "SELECT user_id, username, password_hash FROM users WHERE email = ? OR username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $loginUsername, $loginUsername);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($loginPassword, $user['password_hash'])) {
                setcookie("username", $user['username'], time() + (86400 * 30), "/");
                setcookie("email", $loginUsername, time() + (86400 * 30), "/");                

                echo "<script>alert('Login successful!'); window.location.href='home.php';</script>";
            } else {
                echo "<script>alert('Invalid password. Try again.'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('User not found. Please sign up.'); window.location.href='index.php';</script>";
        }
        mysqli_stmt_close($stmt);
    }
}
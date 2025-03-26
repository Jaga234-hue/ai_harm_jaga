<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .toggle-form {
            text-align: center;
            margin-top: 10px;
        }
        .toggle-form a {
            color: #007bff;
            text-decoration: none;
        }
        .toggle-form a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<body>

<div class="form-container" id="loginForm">
    <h2>Login</h2>
    <form action="userdata.php" method="POST">
        <input type="text" name="loginUsername" placeholder="Username or Email" required>
        <input type="password" name="loginPassword" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div class="toggle-form">
        <span>Don't have an account? <a href="#" onclick="toggleForm('signupForm')">Sign up</a></span>
    </div>
</div>

<div class="form-container" id="signupForm" style="display: none;">
    <h2>Sign Up</h2>
    <form id="signupFormElement" action="userdata.php" method="POST" onsubmit="return validatePassword()">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
        <div class="error-message" id="passwordError">Passwords do not match.</div>
        <button type="submit">Sign Up</button>
    </form>
    <div class="toggle-form">
        <span>Already have an account? <a href="#" onclick="toggleForm('loginForm')">Login</a></span>
    </div>
</div>

<script>
    function toggleForm(formId) {
        document.getElementById('loginForm').style.display = formId === 'loginForm' ? 'block' : 'none';
        document.getElementById('signupForm').style.display = formId === 'signupForm' ? 'block' : 'none';
    }

    function validatePassword() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const passwordError = document.getElementById('passwordError');

        if (password !== confirmPassword) {
            passwordError.style.display = 'block';
            return false; // Prevent form submission
        } else {
            passwordError.style.display = 'none';
            return true; // Allow form submission
        }
    }
</script>

</body>
</html>
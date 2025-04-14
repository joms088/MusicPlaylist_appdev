<?php
require "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"]; // Plain text password
    $confirmpassword = $_POST["confirm-password"];

    // Compare plain text passwords
    if ($password !== $confirmpassword) {
        echo "<script>alert('Passwords does not match!'); window.location.href = 'signup.php';</script>";
        exit();
    }

    // Hash the password after comparison
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered.'); window.location.href = 'signup.php';</script>";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $username, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Signup successful! You can now login'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Could not create account'); window.location.href = 'signup.php';</script>";
        }
        $stmt->close();
    }
    $checkEmail->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music System - Sign Up</title>
    <style>
        :root {
            --primary: #00c853;
            --primary-dark: #009624;
            --dark: #333;
            --light: #f5f5f5;
            --error: #f44336;
            --success: #4caf50;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(to bottom, black, gray) no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 520px;
            background: #f5f5f5;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .form-container {
            padding: 40px 60px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            margin-bottom: 15px;
        }
        
        .music-icon {
            width: 60px;
            height: 60px;
            fill: var(--primary);
        }
        
        h1 {
            color: var(--primary);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            color: var(--dark);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        input {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background-color: white;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 200, 83, 0.2);
        }
        
        .button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background-color: var(--primary);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 30px;
        }
        
        .form-switch {
            color: #666;
            font-size: 14px;
        }
        
        .form-switch a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .form-switch a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .music-note {
            position: absolute;
            opacity: 0.1;
            z-index: 0;
        }
        
        .note-1 {
            top: 20px;
            right: 20px;
            font-size: 40px;
            transform: rotate(15deg);
        }
        
        .note-2 {
            bottom: 40px;
            left: 30px;
            font-size: 30px;
            transform: rotate(-10deg);
        }
        
        .error-message {
            color: var(--error);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .success-message {
            color: var(--success);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }
        
        @media (max-width: 480px) {
            .form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <div class="logo">
                    <svg class="music-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                    </svg>
                </div>
                <h1>Join Music</h1>
                <p class="subtitle">Create your account</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm-password')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="button">Sign up</button>
                
                <div class="form-footer">
                    <p class="form-switch">Already have an account? <a href="login.php">Log in</a></p>
                </div>
            </form>
            
            <div class="music-note note-1">♪</div>
            <div class="music-note note-2">♫</div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        document.querySelector('form').addEventListener('submit', function (e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
    </script>
</body>
</html>
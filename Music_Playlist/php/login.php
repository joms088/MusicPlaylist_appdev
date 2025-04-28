<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION["user_id"])) {
    header("Location: home.php");
    exit();
}

require "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            header("Location: ../php/home.php"); 
            exit();
        } else {
            $errorMessage = "Invalid password.";
        }
    } else {
        $errorMessage = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music System - Login</title>
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
            max-width: 420px;
            background: #f5f5f5;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .form-container {
            padding: 40px 30px;
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
            display: none;
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

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
            position: relative;
        }
        .modal-content p {
            margin: 0 0 20px;
            font-size: 16px;
            color: #333;
        }
        .modal-content button {
            padding: 10px 20px;
            background-color: #008C48;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal-content button:hover {
            background-color: #03c969;
        }

        /* Popup Error Text Styles */
        .error-popup {
            color: var(--error);
            font-size: 15px;
            margin-top: 5px;
            display: none;
            background: rgba(244, 67, 54, 0.1);
            padding: 12px 10px;
            border-radius: 4px;
            position: relative;
        }
        .error-popup.visible {
            display: block;
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
                <h1>Welcome to Music</h1>
                <p class="subtitle">Sign in to continue to your account</p>
            </div>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@email.com" >
                    <div class="error-popup" id="emailError">Please enter a valid email address.</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Enter your password" >
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="error-popup" id="passwordError">Password must be at least 5 characters long.</div>
                </div>
                
                <button type="submit" class="button">Login</button>
                
                <div class="form-footer">
                    <p class="form-switch">Don't have an account? <a href="signup.php">Sign up</a></p>
                </div>
            </form>
            
            <div class="music-note note-1">♪</div>
            <div class="music-note note-2">♫</div>
        </div>
    </div>
    
    <!-- Modal for Messages -->
    <div class="modal-overlay" id="messageModal">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <button onclick="closeModal('messageModal')">OK</button>
        </div>
    </div>
    
    <script>
        // Function to show modal with a message
        function showModal(message, modalId = 'messageModal', redirect = null) {
            const modal = document.getElementById(modalId);
            const modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = message;
            modal.style.display = 'flex';
            if (redirect) {
                setTimeout(() => {
                    window.location.href = redirect;
                }, 2000);
            }
        }

        // Function to close modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }

        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        // Validation Functions
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function validatePassword(password) {
            return password.length >= 5;
        }

        // Show/Hide Error Popup
        function showError(elementId, show) {
            const errorElement = document.getElementById(elementId);
            if (show) {
                errorElement.classList.add('visible');
            } else {
                errorElement.classList.remove('visible');
            }
        }

        // Real-time validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value.trim();
            showError('emailError', !validateEmail(email) && email !== '');
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            showError('passwordError', !validatePassword(password) && password !== '');
        });

        // Form submission validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            let hasError = false;

            if (!validateEmail(email)) {
                showError('emailError', true);
                hasError = true;
            }

            if (!validatePassword(password)) {
                showError('passwordError', true);
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });

        // Show error messages from PHP
        <?php if (isset($errorMessage)) { ?>
            showModal("<?php echo $errorMessage; ?>", 'messageModal', 'login.php');
        <?php } ?>
    </script>
</body>
</html>
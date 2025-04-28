<?php
include("db_connection.php");
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION["user_id"];

// Fetch user details
$sql_user = "SELECT username, email FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$stmt_user->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" 
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Profile</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        background: linear-gradient(to bottom, black, gray) no-repeat;
        height: 100%;
        background-size: cover;
        background-position: center;
    }

    nav {
        width: 20%;
        background-color: #000000;
        padding: 10px 0;
        height: 100vh;
    }
    .nav_menu {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .nav_menu a {
        color: white;
        text-decoration: none;
        padding: 14px 20px;
        width: 89%;
        text-align: center;
        transition: background-color 0.3s;
    }
    .nav_menu a:hover {
        background-color: #232121;
        border-radius: 5px;
    }

    .content {
        flex: 1;
        padding: 20px;
        color: white;
    }
    .welcome-message {
        font-size: 24px;
        margin: 20px;
    }
    .profile-container {
        max-width: 500px;
        margin: 0 auto;
    }
    .profile-container h2 {
        margin-bottom: 20px;
    }
    .profile-container form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .profile-container input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #333;
        color: white;
        outline: none;
    }
    .profile-container button {
        padding: 10px;
        background-color: #008C48;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .profile-container button:hover {
        background-color: #03c969;
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
</style>
</head>
<body>
    <nav>
        <div class="nav_menu">
            <a href="profile.php"><i class="uil uil-user"></i> Profile</a>
            <a href="home.php"><i class="uil uil-estate"></i> Home</a>
            <a href="add_song.php"><i class="uil uil-music"></i> Add Song</a>
            <a href="view_playlist.php"><i class="uil uil-list-ul"></i> View Playlist</a>
        </div>
    </nav>

    <main class="content">
        <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
        <div class="profile-container">
            <h2>Profile</h2>
            <form id="editProfileForm" onsubmit="event.preventDefault(); updateProfile();">
                <h3>Edit Profile</h3>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder="Username" required>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email" required>
                <button type="submit">Update Profile</button>
            </form>
            <form id="changePasswordForm" onsubmit="event.preventDefault(); changePassword();">
                <h3>Change Password</h3>
                <input type="password" id="currentPassword" name="currentPassword" placeholder="Current Password" required>
                <input type="password" id="newPassword" name="newPassword" placeholder="New Password" required>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm New Password" required>
                <button type="submit">Change Password</button>
            </form>
        </div>
    </main>

    <!-- Modal for Messages -->
    <div class="modal-overlay" id="messageModal">
        <div class="modal-content">
            <p id="modalMessage"></p>
            <button onclick="closeModal('messageModal')">OK</button>
        </div>
    </div>

<script>
    // Function to show modal with a message
    function showModal(message, modalId = 'messageModal') {
        const modal = document.getElementById(modalId);
        const modalMessage = document.getElementById('modalMessage');
        modalMessage.textContent = message;
        modal.style.display = 'flex';
    }

    // Function to close modal
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'none';
    }

    function updateProfile() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;

        fetch('update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`
        })
        .then(response => response.text())
        .then(result => {
            showModal(result);
            if (result.includes('successfully')) {
                // Update the welcome message
                document.querySelector('.welcome-message').textContent = `Welcome, ${username}!`;
            }
        })
        .catch(error => {
            console.error('Error updating profile:', error);
            showModal('Error updating profile.');
        });
    }

    function changePassword() {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            showModal('New password and confirmation do not match.');
            return;
        }

        fetch('change_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `currentPassword=${encodeURIComponent(currentPassword)}&newPassword=${encodeURIComponent(newPassword)}`
        })
        .then(response => response.text())
        .then(result => {
            showModal(result);
            if (result.includes('successfully')) {
                document.getElementById('changePasswordForm').reset();
            }
        })
        .catch(error => {
            console.error('Error changing password:', error);
            showModal('Error changing password.');
        });
    }
</script>
</body>
</html>
<?php
session_start();
require "db_connection.php"; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["user_id"])) {
        $errorMessage = "You must be logged in to add a song.";
    } else {
        $userId = $_SESSION["user_id"];

        $checkUserStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $checkUserStmt->bind_param("i", $userId);
        $checkUserStmt->execute();
        $result = $checkUserStmt->get_result();

        if ($result->num_rows === 0) {
            $errorMessage = "Invalid user. Please log in again.";
        } else {
            $checkUserStmt->close(); 

            $songTitle = trim($_POST["song_title"]);
            $youtubeLink = trim($_POST["youtube_link"]);

            if (empty($songTitle) || empty($youtubeLink)) {
                $errorMessage = "Song title and YouTube link are required!";
            } else {
                // Function to extract YouTube video ID
                function getYouTubeID($url) {
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches);
                    return $matches[1] ?? null;
                }

                $videoID = getYouTubeID($youtubeLink);
                if (!$videoID) {
                    $errorMessage = "Invalid YouTube link.";
                } else {
                    $thumbnailURL = "https://img.youtube.com/vi/$videoID/hqdefault.jpg";

                    $stmt = $conn->prepare("INSERT INTO songs (song_name, youtube_link, picture, user_id) VALUES (?, ?, ?, ?)");
                    if (!$stmt) {
                        $errorMessage = "Error preparing statement: " . $conn->error;
                    } else {
                        $stmt->bind_param("sssi", $songTitle, $youtubeLink, $thumbnailURL, $userId);
                        
                        if ($stmt->execute()) {
                            header("Location: add_song.php?success=1");
                            exit();
                        } else {
                            $errorMessage = "Error adding song: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

// Fetch user details for profile
$user_id = $_SESSION["user_id"];
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
    <link rel="stylesheet" href="../css/addsong.css">
    <title>Add Song</title>
    
</head>
<body>
    
    <nav>
        <div class="nav_menu">
            <a href="javascript:void(0)" onclick="showProfilePopup()"><i class="uil uil-user"></i> Profile</a>
            <a href="../php/home.php" class="home"><i class="uil uil-estate"></i> Home</a>
            <a href="../php/add_song.php"><i class="uil uil-music"></i> Add Song</a>
            <a href="../php/view_playlist.php"><i class="uil uil-list-ul"></i> View Playlist</a>
        </div>
    </nav>

    <main class="content">
        <div class="add_song_form">
            <form id="addSongForm" action="../php/add_song.php" method="POST">
                <h2>Add a Song</h2>

                <label for="song_title">Song Title & Artists:</label>
                <input type="text" id="song_title" name="song_title" placeholder="Enter Song Title & Artists" required>

                <label for="youtube_link">YouTube Link:</label>
                <input type="text" id="youtube_link" name="youtube_link" placeholder="Enter YouTube Link" required>

                <label>Thumbnail Preview:</label>
                <img id="thumbnailPrevi
ew" src="" alt="YouTube Thumbnail">

                <button class="add_song_btn" type="submit">Add Song</button>
            </form>
        </div>
    </main>

    <!-- Profile Popup -->
    <div id="profilePopup" class="profile_popup">
        <span class="close_btn" onclick="closeProfilePopup()">×</span>
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

    <!-- Modal for Messages -->
    <div class="modal-overlay" id="messageModal-variant">
        <div class="modal-content">
            <p id="modalMessage-variant"></p>
            <button onclick="closeModal('messageModal-variant')">OK</button>
        </div>
    </div>

    <script>
        // Function to show modal with a message
        function showModal(message, modalId = 'messageModal-variant') {
            const modal = document.getElementById(modalId);
            const modalMessage = document.getElementById('modalMessage-variant');
            modalMessage.textContent = message;
            modal.style.display = 'flex';
        }

        // Function to close modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }

        document.getElementById("youtube_link").addEventListener("input", function () {
            let url = this.value;
            let videoID = extractYouTubeID(url);
            if (videoID) {
                document.getElementById("thumbnailPreview").src = `https://img.youtube.com/vi/${videoID}/hqdefault.jpg`;
            }
        });

        function extractYouTubeID(url) {
            let match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/);
            return match ? match[1] : null;
        }

        // Profile Popup Functions
        function showProfilePopup() {
            document.getElementById('profilePopup').style.display = 'block';
        }

        function closeProfilePopup() {
            document.getElementById('profilePopup').style.display = 'none';
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

        document.addEventListener("DOMContentLoaded", function() {
            // Check for success parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                showModal("Song added successfully!");
            }

            // Show any error messages from PHP
            <?php if (isset($errorMessage)) { ?>
                showModal("<?php echo $errorMessage; ?>");
            <?php } ?>
        });
    </script>
</body>
</html>
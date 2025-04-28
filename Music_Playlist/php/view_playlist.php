<?php
include("db_connection.php");
session_start();

$user_id = $_SESSION["user_id"];

// Fetch existing playlists
$sql = "SELECT playlist_id, playlist_name FROM playlists WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$playlists = [];
while ($row = $result->fetch_assoc()) {
    $playlists[] = $row;
}

// Fetch user details for profile
$sql_user = "SELECT username, email FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
$stmt_user->close();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Playlists</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
    integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" 
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/viewplaylist.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
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

    <div class="content">
        <div class="new-playlist-btn" id="openPopup">
            <i class="fa fa-plus"></i> New Playlist
        </div>

        <div class="playlist-list">
        <?php foreach ($playlists as $playlist): ?>
            <div class="playlist-item">
                <a href="../php/view_song.php?playlist_id=<?= $playlist['playlist_id'] ?>" class="PlaylistName_id">
                    <?= htmlspecialchars($playlist['playlist_name']) ?>
                </a>
                <i class="fa fa-trash delete-btn" data-playlist-id="<?= $playlist['playlist_id'] ?>"></i>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

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

    <div class="popup-overlay" id="popup">
        <div class="popup-box">
            <h3>Create New Playlist</h3>
            <input type="text" id="playlistName" placeholder="Enter Playlist Name">
            <div class="popup-buttons">
                <button class="btn-cancel" id="closePopup">Cancel</button>
                <button class="btn-confirm" id="savePlaylist">Confirm</button>
            </div>
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

        $(document).ready(function() {
            $("#openPopup").click(function() {
                $("#popup").css({ "visibility": "visible", "opacity": "1" });
            });

            $("#closePopup").click(function() {
                $("#popup").css({ "visibility": "hidden", "opacity": "0" });
            });

            $("#savePlaylist").click(function() {
                let playlistName = $("#playlistName").val().trim();
                if (playlistName === "") {
                    showModal("Please enter a playlist name.");
                    return;
                }

                $.post("save_playlist.php", { playlist_name: playlistName }, function(response) {
                    if (response === "success") {
                        showModal("Playlist created successfully!");
                        setTimeout(() => {
                            location.reload(); 
                        }, 2000);
                    } else {
                        showModal("Failed to save playlist.");
                    }
                });
            });

            $(".delete-btn").click(function() {
                let playlistId = $(this).data("playlist-id");
                if (confirm("Are you sure you want to delete this playlist?")) {
                    $.post("delete_playlist.php", { playlist_id: playlistId }, function(response) {
                        if (response === "success") {
                            showModal("Playlist deleted successfully.");
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showModal("Failed to delete playlist.");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex; 
            background: linear-gradient(to bottom, black, gray);
            height: 100vh;
        }

        /* Sidebar Navigation */
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
        .home{
            margin-top: 40px;
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
        }

        /* New Playlist Button */
        .new-playlist-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border-radius: 10px;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            width: fit-content;
        }
        .new-playlist-btn:hover{
            background: gray;
            transition: .2s;
        }
        .new-playlist-btn i {
            font-size: 22px;
            color: black;
        }

        /* Playlist List */
        .playlist-list {
            margin-top: 30px;
        }
        .PlaylistName_id{
            color: white;
            text-decoration: none;
        }
        .PlaylistName_id:hover{
            color: #00ccff;
            text-decoration: none;
        }
        .playlist-item {
            font-size: 20px;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            border: 1px solid #444;
            background: rgba(255, 255, 255, 0.1);
            gap: 10px;
            width: 95%;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .playlist-item:hover {
            color: #00ffcc;
        }

        .delete-btn {
            font-size: 22px;
            cursor: pointer;
            color: red;
        }

        /* Popup Form */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .popup-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }
        .popup-box input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: none;
            border-bottom: 2px solid black;
            outline: none;
            background: transparent;
        }
        .popup-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }
        .popup-buttons button {
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-cancel {
            background-color: #8B0000;
            color: white;
            border-radius: 5px;
        }
        .btn-cancel:hover{
            background-color:rgb(101, 2, 2);
        }
        .btn-confirm {
            background-color: #008C48;
            color: white;
            border-radius: 5px;
        }
        .btn-confirm:hover{
            background-color:rgb(1, 87, 45);
            transition: .2s;
        }

        /* Songs Container */
        #songsContainer {
            margin-top: 20px;
            color: white;
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav_menu">
            <a href="../php/home.php" class="home"><i class="uil uil-estate"></i> Home</a>
            <a href="../php/add_song.php"><i class="uil uil-music"></i> Add Song</a>
            <a href="../php/view_playlist.php"><i class="uil uil-list-ul"></i> View Playlist</a>
        </div>
    </nav>

    <div class="content">
        <!-- New Playlist Button -->
        <div class="new-playlist-btn" id="openPopup">
            <i class="fa fa-plus"></i> New Playlist
        </div>

        <!-- Playlist List -->
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

    <!-- Popup Form -->
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

    <script>
        $(document).ready(function() {
            // Show Create Playlist Popup
            $("#openPopup").click(function() {
                $("#popup").css({ "visibility": "visible", "opacity": "1" });
            });

            // Close Popup
            $("#closePopup").click(function() {
                $("#popup").css({ "visibility": "hidden", "opacity": "0" });
            });

            // Save Playlist Using AJAX
            $("#savePlaylist").click(function() {
                let playlistName = $("#playlistName").val().trim();
                if (playlistName === "") {
                    alert("Please enter a playlist name.");
                    return;
                }

                $.post("save_playlist.php", { playlist_name: playlistName }, function(response) {
                    if (response === "success") {
                        alert("Playlist created successfully!");
                        location.reload(); 
                    } else {
                        alert("Failed to save playlist.");
                    }
                });
            });

            // Delete Playlist using AJAX
            $(".delete-btn").click(function() {
                let playlistId = $(this).data("playlist-id");
                if (confirm("Are you sure you want to delete this playlist?")) {
                    $.post("delete_playlist.php", { playlist_id: playlistId }, function(response) {
                        if (response === "success") {
                            alert("Playlist deleted successfully.");
                            location.reload();
                        } else {
                            alert("Failed to delete playlist.");
                        }
                    });
                }
            });
        });
    </script>

</body>
</html>

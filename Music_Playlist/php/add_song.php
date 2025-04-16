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
    <title>Add Song</title>
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
        }
        .home {
            margin-top: 40px;
        }

        .content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .add_song_form {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
            color: white;
            width: 350px;
            text-align: center;
        }
        .add_song_form h2 {
            margin-bottom: 20px;
        }
        .add_song_form label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
        }
        .add_song_form input {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            outline: none;
        }
        .add_song_btn {
            width: 100%;
            padding: 10px;
            background: green;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .add_song_btn:hover {
            background: darkgreen;
        }
        #thumbnailPreview {
            width: 100%;
            border-radius: 5px;
            margin-top: 10px;
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
                <img id="thumbnailPreview" src="" alt="YouTube Thumbnail">

                <button class="add_song_btn" type="submit">Add Song</button>
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
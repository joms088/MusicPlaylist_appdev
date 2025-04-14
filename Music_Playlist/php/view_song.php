<?php
include("db_connection.php");
session_start();

if (!isset($_GET["playlist_id"])) {
    die("Playlist not found.");
}

$playlist_id = $_GET["playlist_id"];

// Fetch playlist name
$sql = "SELECT playlist_name FROM playlists WHERE playlist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$result = $stmt->get_result();
$playlist = $result->fetch_assoc();
$stmt->close();

if (!$playlist) {
    die("Playlist not found.");
}

// Fetch songs inside this playlist using the junction table
$sql = "SELECT s.song_id, s.song_name, s.youtube_link 
        FROM songs s
        JOIN playlist_songs ps ON s.song_id = ps.song_id
        WHERE ps.playlist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$result = $stmt->get_result();
$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($playlist["playlist_name"]) ?> - Songs</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background: linear-gradient(to bottom, black, gray);
            color: white;
        }
        nav {
            width: 20%;
            background-color: #000000;
            padding: 10px 0;
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
        .home {
            margin-top: 40px;
        }
        .content {
            flex: 1;
            padding: 50px;
        }
        h1 {
            color: #00ffcc;
            margin-bottom: 20px;
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        li {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .icon-btns {
            display: flex;
            gap: 20px;
            align-items: center;
            font-size: 20px;
        }
        .icon-btns i {
            cursor: pointer;
            color: white;
        }
        .icon-btns i:hover {
            color: red;
        }
       
        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: #ffffff;
            text-decoration: none;
            background-color: #232121;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #111;
            padding: 90px;
            border-radius: 10px;
            width: 700px;
            max-width: 90%;
            z-index: 9999;
        }
        .popup iframe {
            width: 100%;
            height: 250px;
            border: none;
            margin-bottom: 10px;
        }
        .popup .close-btn {
            background: red;
            color: white;
            padding: 5px 10px;
            border: none;
            float: right;
            cursor: pointer;
            margin-bottom: 20px;
            margin-top: -50px;
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
    <h1><?= htmlspecialchars($playlist["playlist_name"]) ?> - Songs</h1>
    <ul>
        <?php if (count($songs) > 0): ?>
            <?php foreach ($songs as $song): ?>
                <li>
                    <?= htmlspecialchars($song["song_name"]) ?>
                    <div class="icon-btns">
                        <i class="fas fa-play" onclick="openPopup('<?= $song['youtube_link'] ?>')"></i>
                        <i class="fas fa-trash" onclick="deleteSongFromPlaylist(<?= $playlist_id ?>, <?= $song['song_id'] ?>)"></i>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No songs in this playlist.</li>
        <?php endif; ?>
    </ul>
    <a href="view_playlist.php" class="back-link">← Back to Playlists</a>
</div>

<div class="popup" id="songPopup">
    <button class="close-btn" onclick="closePopup()">X</button>
    <iframe id="youtubeFrame" allowfullscreen></iframe>
    <div id="lyricsContainer" style="margin-top: 10px; color: #00ccff;"></div>
</div>

<script>
function openPopup(youtubeUrl) {
    const videoId = extractYouTubeID(youtubeUrl);
    if (!videoId) {
        alert("Invalid YouTube link.");
        return;
    }
    document.getElementById("youtubeFrame").src = `https://www.youtube.com/embed/${videoId}`;
    document.getElementById("songPopup").style.display = "block";
}

function extractYouTubeID(url) {
    const match = url.match(/(?:youtu\.be\/|v=)([^&]+)/);
    return match ? match[1] : null;
}

function closePopup() {
    document.getElementById("songPopup").style.display = "none";
    document.getElementById("youtubeFrame").src = "";
}

function deleteSongFromPlaylist(playlistId, songId) {
    if (confirm("Are you sure you want to remove this song from the playlist?")) {
        fetch("delete_from_playlist.php", {
            method: "POST",
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `playlist_id=${playlistId}&song_id=${songId}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "deleted") {
                alert("Song removed.");
                location.reload();
            } else {
                alert("Error removing song.");
            }
        });
    }
}
</script>

</body>
</html>

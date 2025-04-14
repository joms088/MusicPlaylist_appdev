<?php
include("db_connection.php");
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"]; 

// Fetch songs from database for the logged-in user
$sql = "SELECT song_id, song_name, picture, youtube_link FROM songs WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <title>Home</title>
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

        /* NAVBAR HOME, ADD SONG, VIEW PLAYLIST */
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

        /* MAIN SEARCH BAR */
        .search_container {
            position: relative;
            display: inline-block;
        }
        .search_bar {
            width: 690px; 
            padding: 10px 35px 10px 10px; 
            border: 1px solid #ccc;
            border-radius: 20px;
            outline: none;
            margin-top: 20px;
            margin-left: 150px;
            margin-bottom: 50px;
        }
        .search_icon {
            position: absolute;
            right: 10px;
            top: 35%;
            transform: translateY(-50%);
            color: #555;
            cursor: pointer;
        }

        .btn_logout {
            margin-left: 200px;
            padding: 5px;
            background-color: #008C48;
            border: none;
            border-radius: 10px;
            width: 100px;
            height: 40px;
            color: white;
            font-weight: bold;
        }
        .btn_logout:hover {
            background-color: #03c969;
            cursor: pointer;
        }

        .most_played {
            width: 100%;
            max-height: 200px;
            overflow: hidden;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.1);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
        }

        /* All Songs List */
        .song_list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 10px;
            max-height: 70vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #888 #333;
        }
        .song_list::-webkit-scrollbar {
            width: 8px;
        }

        .song_list::-webkit-scrollbar-track {
            background: #333;
            border-radius: 10px;
        }

        .song_list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .song_list::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }

        .song_list::before {
            
            color: white;
            text-align: center;
            grid-column: span 2;
        }

        /* Empty placeholders */
        .most_played::before {
            content: "Most Played - No data yet";
            color: #bbb;
            font-size: 18px;
        }
        .welcome-message {
            color: white;
            font-size: 24px;
            margin: 20px;
        }
        .p_song_name{
            color: white;
        }

        /* Song Item */
        .song_item {
            display: flex;
            align-items: center;
            justify-content: space-between; 
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #444;
            background: rgba(255, 255, 255, 0.1);
            gap: 10px;
            width: 95%;
            cursor: pointer;
        }
        /* Song Image */
        .song_img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
        }

        /* Song Name */
        .p_song_name {
            flex-grow: 1;
            color: white;
            font-size: 16px;
        }

        /* Song Actions */
        .song_actions {
            display: flex;
            gap: 8px;
        }

        /* Icon Buttons */
        .icon_btn {
            border: none;
            padding: 8px;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s ease-in-out;
        }

        /* Green for Add Button */
        .icon_add {
            background-color: #008C48;
            color: white;
        }

        .icon_add:hover {
            background-color: #03c969;
        }

        /* Red for Remove Button */
        .icon_remove {
            background-color: #8B0000;
            color: white;
        }

        .icon_remove:hover {
            background-color: #FF0000;
        }

        /* popup embedded video player */
        .popup_player {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 90px;
            border-radius: 10px;
            width: 700px;
            text-align: center;
        }
        .popup_player img {
            width: 60%;
            height: 100%;
            border-radius: 10px;
        }
        .popup_player .close_btn {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 30px;
        }
        .p_no_songs_available{
            color: white;
        }
        #lyricsContainer {
            display: none; 
            max-height: 200px; 
            overflow-y: auto;
            text-align: center;
            font-size: 16px;
            color: white;
            margin-top: 20px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 5px;
            width: 100%;
            white-space: pre-line; 
        }


        #lyricsText {
            max-height: 300px; 
            white-space: pre-wrap; 
        }

        @keyframes scrollLyrics {
            0% { transform: translateY(0); }
            100% { transform: translateY(-100%); }
        }

        .playlist_popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
        }

        .playlist_popup select {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }

        .playlist_popup button {
            background-color: #008C48;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 10px;
            cursor: pointer;
        }

        .playlist_popup .close_btn {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 30px;
        }
</style>
</head>
<body>
    <nav>
        <div class="nav_menu">
            <a href="home.php" class="home"><i class="uil uil-estate"></i> Home</a>
            <a href="add_song.php"><i class="uil uil-music"></i> Add Song</a>
            <a href="view_playlist.php"><i class="uil uil-list-ul"></i> View Playlist</a>
        </div>
    </nav>

    <main class="content">
    <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>

        <div class="search_container">
            <input type="search" id="search" placeholder="Search" class="search_bar">
            <i class="fas fa-search search_icon"></i> 
        </div>
        <button class="btn_logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Log out</button>
        
        <!-- <div class="most_played" id="mostPlayed"> -->
            
        </div>
        <div class="song_list" id="songList">
        <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='song_item'>";
                    echo "<img src='" . htmlspecialchars($row["picture"]) . "' alt='Song Image' class='song_img'>";
                    echo "<p class='p_song_name'>" . htmlspecialchars($row["song_name"]) . "</p>";
                    echo "<div class='song_actions'>";
                    echo '<button class="icon_btn icon_play" onclick="showPlayer(' . 
                    '\'' . htmlspecialchars($row["picture"]) . '\', ' . 
                    '\'' . htmlspecialchars($row["song_name"]) . '\', ' . 
                    '\'' . htmlspecialchars($row["youtube_link"]) . '\'' . 
                    ')"><i class="fas fa-play"></i></button>';
                
                    echo "<button class='icon_btn icon_add' onclick='addToPlaylist(" . $row["song_id"] . ")'><i class='fas fa-plus'></i></button>";
                    echo "<button class='icon_btn icon_remove' onclick='deleteSong(" . $row["song_id"] . ")'><i class='fas fa-trash'></i></button>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p class='p_no_songs_available'>No songs available.</p>";
            }
            echo "</div>";
            
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </main>

    <!-- POPUP VIDEO PLAYER -->
    <div class="popup_player" id="popupPlayer">
    <span class="close_btn" onclick="closePlayer()">&times;</span>
    <h3 id="playerSongName"></h3>
    <div id="playerIframeContainer"></div> 
    <div id="lyricsContainer">
        <h4>Lyrics</h4>
        <pre id="lyricsText" style="color: white; white-space: pre-wrap; text-align: left;"></pre>
    </div>
</div>
</div>

<!-- POPUP PLAYLIST -->
<div id="playlistPopup" class="playlist_popup">
    <span class="close_btn" onclick="closePlaylistPopup()">&times;</span>
    <h2>Select a Playlist</h2>
    <div id="playlistContainer">
        <select id="playlistSelect">
            <option value="">Select a Playlist</option>
        </select>
    </div>
    <button onclick="confirmAddToPlaylist()">Add to Playlist</button>
</div>

<!-- Playlist Popup -->
<!-- <div id="selectPlaylistPopup" style="display:none;">
  <label>Select Playlist:</label>
  <select id="playlistSelect"> -->
    <!-- Dynamically fill playlists here -->
  <!-- </select>
  <button id="confirmAddToPlaylist">Add</button>
</div> -->

<script src="script.js"></script>
</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function () {
            if (document.referrer === "" || document.referrer.indexOf(window.location.hostname) === -1) {
                alert("Direct access is not allowed!");
                window.location.href = "login.php"; 
            }
        });

        function logout() {
            let confirmAction = confirm('Are you sure you want to Logout?');
            if (confirmAction) {
                window.location.href = "../php/logout.php"; 
            }
        }

        let selectedSongId = null;

    function addToPlaylist(songId) {
            selectedSongId = songId; 
    document.getElementById("playlistPopup").style.display = "block";

    fetch("get_playlists.php") 
        .then(response => response.json())
        .then(data => {
            let dropdown = document.getElementById("playlistSelect");
            dropdown.innerHTML = '<option value="">Select a Playlist</option>'; 

            data.forEach(playlist => {
                let option = document.createElement("option");
                option.value = playlist.playlist_id;
                option.textContent = playlist.playlist_name;
                dropdown.appendChild(option);
            });
        })
        .catch(error => console.error("Error fetching playlists:", error));
    }
    function confirmAddToPlaylist() {
    let playlistId = document.getElementById("playlistSelect").value;

    if (!playlistId) {
        alert("Please select a playlist.");
        return;
    }

    fetch("add_to_playlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `song_id=${selectedSongId}&playlist_id=${playlistId}`
    })
    .then(response => response.text())
    .then(result => {
        alert(result);
        closePlaylistPopup();
    })
    .catch(error => console.error("Error adding song to playlist:", error));
}

function closePlaylistPopup() {
    document.getElementById("playlistPopup").style.display = "none";
}

    function removeSong(songId) {
        let confirmAction = confirm('Are you sure you want to remove this song?');
        if (confirmAction) {
            window.location.href = "remove_song.php?song_id=" + songId;
        }
    }

    function showPlayer(image, name, youtubeLink) {
    let youtubeId = extractYouTubeId(youtubeLink);
    if (!youtubeId) {
        alert("Invalid YouTube Link!");
        return;
    }

    document.getElementById('popupPlayer').style.display = 'block';
    document.getElementById('playerSongName').innerText = name;

    let iframe = document.createElement('iframe');
    iframe.width = "100%";
    iframe.height = "200"; 
    iframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1&controls=1"; 
    iframe.frameBorder = "0";
    iframe.allow = "autoplay; encrypted-media";
    iframe.allowFullscreen = true;

    let playerContainer = document.getElementById('playerIframeContainer');
    playerContainer.innerHTML = ""; 
    playerContainer.appendChild(iframe);

    document.getElementById('lyricsContainer').style.display = 'block';

    // Extract artist and song title
    let parts = name.split(" - "); 
    
    if (parts.length >= 2) {
        var artist = parts[0].trim(); 
        var title = parts[1].trim();  
    } else {
        var title = name.trim(); 
        var artist = ""; 
    }

    console.log("Searching lyrics for:", title, "by", artist); // Debugging

    // Only fetch if we have both artist and title
    if (!artist || !title) {
        document.getElementById('lyricsText').innerText = "Please format song names as 'Artist - Title' for lyrics.";
        return;
    }

    // Add loading message
    document.getElementById('lyricsText').innerText = "Loading lyrics...";

    fetch(`https://api.lyrics.ovh/v1/${encodeURIComponent(artist)}/${encodeURIComponent(title)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Lyrics not available');
            }
            return response.json();
        })
        .then(data => {
            if (data.lyrics) {
                document.getElementById('lyricsText').innerText = data.lyrics;
            } else {
                document.getElementById('lyricsText').innerText = "Lyrics not found.";
            }
        })
        .catch(error => {
            console.error("Error fetching lyrics:", error);
            document.getElementById('lyricsText').innerText = "Lyrics not available.";
        });
}


// Function to extract YouTube Video ID from a URL
function extractYouTubeId(url) {
    let match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
    return match ? match[1] : null;
}

function closePlayer() {
    document.getElementById('popupPlayer').style.display = 'none';
    document.getElementById('playerIframeContainer').innerHTML = "";
}

// Search functionality for home.php
document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.getElementById('search');
    const songItems = document.querySelectorAll('.song_item');
    
    searchBar.addEventListener('keyup', function() {
        const searchTerm = searchBar.value.toLowerCase();
        
        // Filter songs based on search term
        songItems.forEach(item => {
            const songName = item.querySelector('.p_song_name').textContent.toLowerCase();
            
            if (songName.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show a message if no songs match the search
        let visibleSongs = 0;
        songItems.forEach(item => {
            if (item.style.display !== 'none') {
                visibleSongs++;
            }
        });
        
        // Check if "No results" message already exists
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (visibleSongs === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('p');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.className = 'p_no_songs_available';
                noResultsMsg.textContent = 'No songs matching your search.';
                document.getElementById('songList').appendChild(noResultsMsg);
            }
        } else {
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    });
    
    // Clear search functionality when clicking the search icon
    const searchIcon = document.querySelector('.search_icon');
    searchIcon.addEventListener('click', function() {
        searchBar.value = '';

        searchBar.dispatchEvent(new Event('keyup'));
    });
});
</script>

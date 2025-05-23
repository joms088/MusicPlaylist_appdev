<?php
include("db_connection.php");
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
// updated with session
$user_id = $_SESSION["user_id"]; 

// Fetch songs from database for the logged-in user
$sql = "SELECT song_id, song_name, picture, youtube_link FROM songs WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user details for profile
$sql_user = "SELECT username, email FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();
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
    <link rel="stylesheet" href="../css/home.css">
    <title>Home</title>
    
</head>
<body>
    <nav>
        <div class="nav_menu">
            <a href="profile.php"><i class="uil uil-user"></i> Profile</a>
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
        
        <div class="song_list" id="songList">
        <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $song_name = htmlspecialchars($row["song_name"]);
                    $parts = explode(" - ", $song_name);
                    $artist = isset($parts[0]) ? $parts[0] : "";
                    $title = isset($parts[1]) ? $parts[1] : $song_name;
                    echo "<div class='song_item'>";
                    echo "<img src='" . htmlspecialchars($row["picture"]) . "' alt='Song Image' class='song_img'>";
                    echo "<div class='song_info'>";
                    echo "<p class='p_song_title'>" . htmlspecialchars($title) . "</p>";
                    echo "<p class='p_song_artist'>" . htmlspecialchars($artist) . "</p>";
                    echo "</div>";
                    echo "<div class='song_actions'>";
                    echo '<button class="icon_btn icon_play" onclick="showPlayer(' . 
                    '\'' . htmlspecialchars($row["picture"]) . '\', ' . 
                    '\'' . $song_name . '\', ' . 
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
            $stmt_user->close();
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

    document.addEventListener("DOMContentLoaded", function () {
        if (document.referrer === "" || document.referrer.indexOf(window.location.hostname) === -1) {
            showModal("Direct access is not allowed!");
            setTimeout(() => {
                window.location.href = "login.php"; 
            }, 2000);
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
            .catch(error => {
                console.error("Error fetching playlists:", error);
                showModal("Error fetching playlists.");
            });
    }

    function confirmAddToPlaylist() {
        let playlistId = document.getElementById("playlistSelect").value;

        if (!playlistId) {
            showModal("Please select a playlist.");
            return;
        }

        fetch("add_to_playlist.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `song_id=${selectedSongId}&playlist_id=${playlistId}`
        })
        .then(response => response.text())
        .then(result => {
            showModal(result);
            closePlaylistPopup();
        })
        .catch(error => {
            console.error("Error adding song to playlist:", error);
            showModal("Error adding song to playlist.");
        });
    }

    function closePlaylistPopup() {
        document.getElementById("playlistPopup").style.display = "none";
    }

    function deleteSong(songId) {
        let confirmAction = confirm('Are you sure you want to remove this song?');
        if (confirmAction) {
            window.location.href = "remove_song.php?song_id=" + songId;
        }
    }

    function showPlayer(image, name, youtubeLink) {
        let youtubeId = extractYouTubeId(youtubeLink);
        if (!youtubeId) {
            showModal("Invalid YouTube Link!");
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

        let parts = name.split(" - "); 
        
        if (parts.length >= 2) {
            var artist = parts[0].trim(); 
            var title = parts[1].trim();  
        } else {
            var title = name.trim(); 
            var artist = ""; 
        }

        console.log("Searching lyrics for:", title, "by", artist);

        if (!artist || !title) {
            document.getElementById('lyricsText').innerText = "Please format song names as 'Artist - Title' for lyrics.";
            return;
        }

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

    function extractYouTubeId(url) {
        let match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
        return match ? match[1] : null;
    }

    function closePlayer() {
        document.getElementById('popupPlayer').style.display = 'none';
        document.getElementById('playerIframeContainer').innerHTML = "";
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchBar = document.getElementById('search');
        const songItems = document.querySelectorAll('.song_item');
        
        searchBar.addEventListener('keyup', function() {
            const searchTerm = searchBar.value.toLowerCase();
            
            songItems.forEach(item => {
                const songTitle = item.querySelector('.p_song_title').textContent.toLowerCase();
                const songArtist = item.querySelector('.p_song_artist').textContent.toLowerCase();
                
                if (songTitle.includes(searchTerm) || songArtist.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
            
            let visibleSongs = 0;
            songItems.forEach(item => {
                if (item.style.display !== 'none') {
                    visibleSongs++;
                }
            });
            
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
        
        const searchIcon = document.querySelector('.search_icon');
        searchIcon.addEventListener('click', function() {
            searchBar.value = '';
            searchBar.dispatchEvent(new Event('keyup'));
        });
    });
</script>
</body>
</html>
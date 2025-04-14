document.addEventListener("DOMContentLoaded", function () {
    loadPlaylists();
});

function loadPlaylists() {
    fetch("fetch_playlists.php")
        .then(response => response.json())
        .then(playlists => {
            let playlistDropdown = document.getElementById("playlist");

            if (!playlistDropdown) {
                console.error("Playlist dropdown element not found.");
                return;
            }

            playlistDropdown.innerHTML = '<option value="">Select a Playlist</option>'; // Clear old options

            playlists.forEach(playlist => {
                let option = document.createElement("option");
                option.value = playlist.playlist_id;
                option.textContent = playlist.playlist_name;
                playlistDropdown.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error loading playlists:", error);
            alert("Failed to load playlists. Please try again.");
        });
}

function addSong(event) {
    event.preventDefault(); // Prevent form submission

    let playlistId = document.getElementById("playlist")?.value;
    let songTitle = document.getElementById("song_title")?.value.trim();
    let youtubeLink = document.getElementById("youtube_link")?.value.trim();
    let pictureInput = document.getElementById("upload_picture")?.files[0];

    if (!playlistId || songTitle === "" || youtubeLink === "" || !pictureInput) {
        alert("Please fill in all fields.");
        return;
    }

    let formData = new FormData();
    formData.append("playlist_id", playlistId);
    formData.append("song_title", songTitle);
    formData.append("youtube_link", youtubeLink);
    formData.append("upload_picture", pictureInput);

    fetch("add_song.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Show success or error message

        // Clear the form instead of refreshing the page
        document.getElementById("addSongForm").reset();
    })
    .catch(error => {
        console.error("Error adding song:", error);
        alert("Failed to add song. Please try again.");
    });
}

// Attach event listener to the form
document.getElementById("addSongForm").addEventListener("submit", addSong);

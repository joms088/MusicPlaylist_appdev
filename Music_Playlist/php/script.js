function deleteSong(songId) {
  if (confirm("Are you sure you want to delete this song?")) {
    fetch('../php/delete_song.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `song_id=${encodeURIComponent(songId)}`
    })
    .then(res => res.text())
    .then(data => {
      if (data.trim() === "success") {
        alert("Song deleted.");
        displaySongs(); // Refresh song list
      } else {
        alert("Failed to delete song.");
      }
    });
  }
}

function addToPlaylist(songId) {
  const popup = document.getElementById('playlistPopup');
  const confirmBtn = document.getElementById('confirmAddToPlaylist');
  const select = document.getElementById('playlistSelect');

  popup.style.display = 'block';
  confirmBtn.setAttribute('data-song-id', songId); // Store the song ID to use later

  // Fetch playlists from server
  fetch('../php/get_playlists.php')
    .then(response => response.json())
    .then(playlists => {
      select.innerHTML = ''; // Clear existing options

      if (playlists.length > 0) {
        playlists.forEach(playlist => {
          const option = document.createElement('option');
          option.value = playlist.playlist_id;
          option.textContent = playlist.playlist_name;
          select.appendChild(option);
        });
      } else {
        const option = document.createElement('option');
        option.textContent = "No playlists found";
        option.disabled = true;
        select.appendChild(option);
      }
    })
    .catch(error => {
      console.error("Error loading playlists:", error);
    });
}

// Handle actual add to playlist action
document.getElementById('confirmAddToPlaylist').addEventListener('click', function () {
  const songId = this.getAttribute('data-song-id');
  const playlistId = document.getElementById('playlistSelect').value;

  fetch('add_to_playlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `song_id=${encodeURIComponent(songId)}&playlist_id=${encodeURIComponent(playlistId)}`
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() === "added") {
      alert("Song added to playlist!");
    } else if (response.trim() === "already_exists") {
      alert("This song is already in the playlist.");
    } else {
      alert("Error adding song to playlist.");
    }
    document.getElementById('playlistPopup').style.display = 'none';
  })
  .catch(err => {
    alert("Error adding to playlist.");
    console.error(err);
  });
});

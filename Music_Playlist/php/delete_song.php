<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $song_id = $_POST['song_id'];

    // Delete song from playlist_songs
   

    // Delete song from songs
    $deleteSong = "DELETE FROM songs WHERE song_id = ?";
    $stmt2 = $conn->prepare($deleteSong);
    $stmt2->bind_param("i", $song_id);
    $stmt2->execute();

    echo "success";
}
?>

<?php
include("db_connection.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $song_id = $_POST["song_id"];
    $playlist_id = $_POST["playlist_id"];

    $sql = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $playlist_id, $song_id);

    if ($stmt->execute()) {
        echo "Song added to playlist successfully!";
    } else {
        echo "Error adding song to playlist.";
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
include 'db_connection.php';
session_start();

// Validate input
if (!isset($_POST['song_id']) || !isset($_POST['playlist_id'])) {
    echo "invalid_input";
    exit;
}

$song_id = $_POST['song_id'];
$playlist_id = $_POST['playlist_id'];

// Delete the song from the playlist
$sql = "DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $playlist_id, $song_id);

if ($stmt->execute()) {
    echo "deleted";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>

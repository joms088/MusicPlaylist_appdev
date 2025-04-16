<?php
include("db_connection.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "Please log in first";
    exit();
}

$user_id = $_SESSION["user_id"];

// Check if all required parameters are provided
if (!isset($_POST["song_id"]) || !isset($_POST["playlist_id"])) {
    echo "Missing required parameters";
    exit();
}

$song_id = $_POST["song_id"];
$playlist_id = $_POST["playlist_id"];

// Verify that the playlist belongs to the current user
$verify_sql = "SELECT user_id FROM playlists WHERE playlist_id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("i", $playlist_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows == 0) {
    echo "Playlist not found";
    $verify_stmt->close();
    $conn->close();
    exit();
}

$playlist_row = $verify_result->fetch_assoc();
if ($playlist_row["user_id"] != $user_id) {
    echo "You don't have permission to add songs to this playlist";
    $verify_stmt->close();
    $conn->close();
    exit();
}

// Check if song already exists in the playlist
$check_sql = "SELECT * FROM playlist_songs WHERE playlist_id = ? AND song_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $playlist_id, $song_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "This song is already in the playlist";
    $check_stmt->close();
    $verify_stmt->close();
    $conn->close();
    exit();
}

// Add song to playlist
$insert_sql = "INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("ii", $playlist_id, $song_id);

if ($insert_stmt->execute()) {
    echo "Song successfully added to playlist";
} else {
    echo "Error adding song to playlist: " . $conn->error;
}

$insert_stmt->close();
$check_stmt->close();
$verify_stmt->close();
$conn->close();
?>
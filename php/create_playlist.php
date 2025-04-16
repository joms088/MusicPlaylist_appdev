<?php
include("db_connection.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $playlist_name = $_POST["playlist_name"];

    $sql = "INSERT INTO playlists (user_id, playlist_name, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $playlist_name);

    if ($stmt->execute()) {
        echo "Playlist created successfully!";
    } else {
        echo "Error creating playlist.";
    }

    $stmt->close();
    $conn->close();
}
?>

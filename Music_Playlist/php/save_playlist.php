<?php
include("db_connection.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["playlist_name"])) {
    $user_id = $_SESSION["user_id"];
    $playlist_name = trim($_POST["playlist_name"]);

    if (empty($playlist_name)) {
        echo "error";
        exit;
    }

    // Insert the new playlist into the database
    $sql = "INSERT INTO playlists (user_id, playlist_name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $playlist_name);

    if ($stmt->execute()) {
        echo "success"; 
    } else {
        echo "error"; 
    }

    $stmt->close();
    $conn->close();
} else {
    echo "error"; 
}
?>

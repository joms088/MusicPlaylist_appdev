<?php
include("db_connection.php");
session_start();

// Make sure a user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION["user_id"];

// Only fetch playlists for the currently logged-in user
$sql = "SELECT playlist_id, playlist_name FROM playlists WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$playlists = [];
while ($row = $result->fetch_assoc()) {
    $playlists[] = $row;
}

header('Content-Type: application/json');
echo json_encode($playlists);

$stmt->close();
$conn->close();
?>
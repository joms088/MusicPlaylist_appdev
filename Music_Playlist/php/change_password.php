<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to change your password.";
    exit();
}

$user_id = $_SESSION["user_id"];
$currentPassword = $_POST["currentPassword"];
$newPassword = $_POST["newPassword"];

if (empty($currentPassword) || empty($newPassword)) {
    echo "All fields are required.";
    exit();
}

// Verify current password
$sql = "SELECT password FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!password_verify($currentPassword, $user["password"])) {
    echo "Current password is incorrect.";
    $stmt->close();
    $conn->close();
    exit();
}

// Hash new password
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

$sql_update = "UPDATE users SET password = ? WHERE user_id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $newPasswordHash, $user_id);

if ($stmt_update->execute()) {
    echo "Password changed successfully!";
} else {
    echo "Error changing password.";
}

$stmt->close();
$stmt_update->close();
$conn->close();
?>
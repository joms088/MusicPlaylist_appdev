<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to update your profile.";
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_POST["username"];
$email = $_POST["email"];

if (empty($username) || empty($email)) {
    echo "All fields are required.";
    exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    exit();
}

// Check if username or email already exists (excluding current user)
$sql_check = "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ssi", $username, $email, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "Username or email already taken.";
    $stmt_check->close();
    $conn->close();
    exit();
}

$sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $username, $email, $user_id);

if ($stmt->execute()) {
    $_SESSION["username"] = $username; // Update session
    echo "Profile updated successfully!";
} else {
    echo "Error updating profile.";
}

$stmt->close();
$stmt_check->close();
$conn->close();
?>
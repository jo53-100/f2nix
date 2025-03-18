<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Check if a user exists and how their password is stored
$username = "galen"; // Replace with a username you know exists
$query = "SELECT username, password FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "Username: " . $user['username'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
    echo "Hash length: " . strlen($user['password']) . "<br>";

    // Check if it looks like a PHP password_hash result
    if(strpos($user['password'], '$2y$') === 0) {
        echo "This appears to be a proper bcrypt hash.";
    } else {
        echo "This does NOT appear to be a proper bcrypt hash.";
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>
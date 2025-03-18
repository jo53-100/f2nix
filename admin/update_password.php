<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Only allow this script to run locally for security
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die("This script can only be run locally");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    // Hash the password properly using PHP's password_hash
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $query = "UPDATE users SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $hashed_password, $username);

    if ($stmt->execute()) {
        echo "<div style='color:green'>Password updated successfully for user: " . htmlspecialchars($username) . "</div>";
    } else {
        echo "<div style='color:red'>Error updating password: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; }
        button { padding: 10px 15px; background: #4285f4; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Update Password</h1>
    <p>Use this form to update a user's password with proper hashing.</p>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>

        <button type="submit">Update Password</button>
    </form>
</body>
</html>
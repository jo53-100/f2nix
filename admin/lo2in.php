<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// For debugging only - remove for production
$debug_mode = true;
function debug_msg($message) {
    global $debug_mode;
    if($debug_mode) {
        echo "<div style='border:1px solid red; padding:5px; margin:5px; background:#ffeeee;'>";
        echo "<strong>Debug:</strong> " . $message;
        echo "</div>";
    }
}

// Redirect if already logged in
if(is_logged_in()) {
    debug_msg("User already logged in. Redirecting to index.php");
    header('Location: index.php');
    exit;
}

$error = '';

// Process login form
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    debug_msg("Login attempt with username: " . $username);

    if(empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
        debug_msg("Empty username or password");
    } else {
        // Check user credentials
        $query = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        debug_msg("Query executed. Found rows: " . $result->num_rows);

        if($result->num_rows === 1) {
            $user = $result->fetch_assoc();
             debug_msg("User found in database. Stored password hash: " . substr($user['password'], 0, 10) . "...");

            // Verify password

             $password_verified = password_verify($password, $user['password']);
            debug_msg("Password verification result: " . ($password_verified ? "SUCCESS" : "FAILED"));


            if(password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                debug_msg("Login successful. User ID: " . $user['id'] . ", Role: " . $user['role']);
                debug_msg("Session variables set. Redirecting to index.php");

                // Redirect to admin dashboard
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password';
                debug_msg("Password verification failed");
            }
        } else {
            $error = 'Invalid username or password';
            debug_msg("Username not found in database");
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - University News</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>Admin Login</h1>

            <?php if(!empty($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login</button>
            </form>

            <div class="back-link">
                <a href="../index.php">Back to Homepage</a>
            </div>
        </div>
    </div>
</body>
</html>
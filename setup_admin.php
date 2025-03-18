<?php
include 'includes/config.php';
include 'includes/db_connect.php';

// Create users table if it doesn't exist
$create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($create_users_table) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if admin user exists
$check_admin = "SELECT COUNT(*) as count FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Create default admin user
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT); // Default password: admin123
    
    $insert_admin = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
    $stmt = $conn->prepare($insert_admin);
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<strong>Please change this password after first login!</strong><br>";
    } else {
        echo "Error creating admin user: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

echo "<br>Setup complete. You can now <a href='admin/lo2in.php'>login to the admin panel</a>.";
?> 
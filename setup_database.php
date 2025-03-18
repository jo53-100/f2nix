<?php
include 'includes/config.php';
include 'includes/db_connect.php';

// Create articles table if it doesn't exist
$create_articles_table = "CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    date_published DATETIME DEFAULT CURRENT_TIMESTAMP,
    summary TEXT,
    category VARCHAR(50)
)";

if ($conn->query($create_articles_table) === TRUE) {
    echo "Articles table created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert a sample article if the table is empty
$check_empty = "SELECT COUNT(*) as count FROM articles";
$result = $conn->query($check_empty);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    $insert_sample = "INSERT INTO articles (title, image_url, summary, category) VALUES (
        'Welcome to Our New Website',
        'images/sample-image.jpg',
        'This is our first article on the new website. We are excited to share news and updates with our community.',
        'Campus'
    )";
    
    if ($conn->query($insert_sample) === TRUE) {
        echo "Sample article created successfully<br>";
    } else {
        echo "Error creating sample article: " . $conn->error . "<br>";
    }
}

echo "Database setup complete. You can now view the website.";
?> 
<?php
include 'includes/config.php';
include 'includes/db_connect.php';

echo "<h2>Articles in Database:</h2>";

$query = "SELECT * FROM articles ORDER BY date_published DESC";
$result = $conn->query($query);

if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>Date</th><th>Image</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_published']) . "</td>";
            echo "<td>" . htmlspecialchars($row['image_url']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No articles found in the database.";
    }
} else {
    echo "Error querying database: " . $conn->error;
}
?> 
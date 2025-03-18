<?php
// Set current page
$current_page = 'home';

// Database connection
include 'includes/config.php';
include 'includes/db_connect.php';

// Get recent articles
$query = "SELECT id, title, image_url, video_url, date_published, summary, category 
          FROM articles 
          ORDER BY date_published DESC 
          LIMIT 6";
$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Include the header
include 'includes/header.php';
?>

<main class="container">
    <div class="news-grid">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($article = mysqli_fetch_assoc($result)) {
                // Format date
                $date = new DateTime($article['date_published']);
                $formatted_date = $date->format('F j, Y');
                
                // Determine if it has video
                $has_video = !empty($article['video_url']);
                
                // Start news card
                echo '<div class="news-card">';
                
                // Add image or video container
                if (!empty($article['image_url'])) {
                    if ($has_video) {
                        echo '<div class="video-container">';
                        echo '<img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '">';
                        echo '</div>';
                    } else {
                        echo '<div class="news-image">';
                        echo '<img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '">';
                        echo '</div>';
                    }
                }
                
                // Add content
                echo '<div class="news-content">';
                echo '<h2>' . htmlspecialchars($article['title']) . '</h2>';
                echo '<div class="date">' . $formatted_date . '</div>';
                echo '<p>' . htmlspecialchars($article['summary']) . '</p>';
                
                $link_text = $has_video ? 'Watch Video' : 'Read More';
                echo '<a href="article.php?id=' . $article['id'] . '" class="read-more">' . $link_text . '</a>';
                echo '</div></div>';
            }
        } else {
            echo '<p>No articles found. Please add some articles to your database.</p>';
        }
        ?>
    </div>
</main>

<?php
// Include the footer
include 'includes/footer.php';
?> 
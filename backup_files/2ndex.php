<?php
// Database connection and initialization
$current_page = 'home';
$page_title = 'University News';

// Include your database connection
include 'includes/config.php';

// Get recent articles query
$sql = "SELECT id, title, image_url, publish_date, excerpt, has_video FROM articles ORDER BY publish_date DESC LIMIT 6";
$result = mysqli_query($conn, $sql);

// Include header
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">University News</div>
                <nav>
                    <ul>
                        <li><a href="index.php" <?php echo ($current_page == 'home') ? 'class="active"' : ''; ?>>Home</a></li>
                        <li><a href="campus.php" <?php echo ($current_page == 'campus') ? 'class="active"' : ''; ?>>Campus</a></li>
                        <li><a href="academic.php" <?php echo ($current_page == 'academic') ? 'class="active"' : ''; ?>>Academic</a></li>
                        <li><a href="sports.php" <?php echo ($current_page == 'sports') ? 'class="active"' : ''; ?>>Sports</a></li>
                        <li><a href="events.php" <?php echo ($current_page == 'events') ? 'class="active"' : ''; ?>>Events</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Latest University News</h1>
            <p>Stay updated with what's happening around campus</p>
        </div>
    </section>

    <main class="container">
        <div class="news-grid">
            <?php
            // Display articles from database
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Format date
                    $date = new DateTime($row['publish_date']);
                    $formatted_date = $date->format('F j, Y');

                    // Start news card
                    echo '<div class="news-card">';

                    // Display image or video container if available
                    if (!empty($row['image_url'])) {
                        if ($row['has_video']) {
                            echo '<div class="video-container">';
                            echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                            echo '</div>';
                        } else {
                            echo '<div class="news-image">';
                            echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                            echo '</div>';
                        }
                    }

                    // Article content
                    echo '<div class="news-content">';
                    echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                    echo '<div class="date">' . $formatted_date . '</div>';
                    echo '<p>' . htmlspecialchars($row['excerpt']) . '</p>';

                    // Link text based on whether it's a video or article
                    $link_text = $row['has_video'] ? 'Watch Video' : 'Read More';
                    echo '<a href="article.php?id=' . $row['id'] . '" class="read-more">' . $link_text . '</a>';
                    echo '</div>'; // Close news-content
                    echo '</div>'; // Close news-card
                }
            } else {
                echo '<p>No articles found</p>';
            }
            ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
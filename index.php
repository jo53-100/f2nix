<?php
// Set current page
$current_page = 'home';

// Database connection
include 'includes/config.php';
include 'includes/db_connect.php';

// Get recent articles with faculty and category information
$query = "SELECT DISTINCT a.id, a.title, a.image_url, a.video_url, a.date_published, a.summary,
          GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR '||') as category_names,
          GROUP_CONCAT(DISTINCT c.slug ORDER BY c.name ASC SEPARATOR '||') as category_slugs,
          f.name as faculty_name, f.slug as faculty_slug
          FROM articles a 
          LEFT JOIN article_categories ac ON a.id = ac.article_id
          LEFT JOIN categories c ON ac.category_slug = c.slug
          LEFT JOIN faculties f ON c.faculty_id = f.id
          GROUP BY a.id
          ORDER BY a.date_published DESC 
          LIMIT 6";
$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Include the header
include 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Últimas Actualizaciones</h1>
        <p>Las publicaciones más recientes de todas las facultades</p>
    </div>
</section>

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
                echo '<div class="article-meta">';
                echo '<span class="date">' . $formatted_date . '</span>';
                
                if (!empty($article['faculty_name'])) {
                    echo ' · <span class="faculty"><a href="faculty.php?slug=' . htmlspecialchars($article['faculty_slug']) . 
                         '">' . htmlspecialchars($article['faculty_name']) . '</a></span>';
                }
                
                // Handle multiple categories
                if (!empty($article['category_names'])) {
                    $category_names = explode('||', $article['category_names']);
                    $category_slugs = explode('||', $article['category_slugs']);
                    echo ' · <span class="categories">';
                    for ($i = 0; $i < count($category_names); $i++) {
                        if ($i > 0) echo ', ';
                        echo '<a href="category.php?slug=' . htmlspecialchars($category_slugs[$i]) . '">' . 
                             htmlspecialchars($category_names[$i]) . '</a>';
                    }
                    echo '</span>';
                }
                
                echo '</div>';
                echo '<p>' . htmlspecialchars($article['summary']) . '</p>';
                
                $link_text = $has_video ? 'Ver Video' : 'Leer Más';
                echo '<a href="article.php?id=' . $article['id'] . '" class="read-more">' . $link_text . '</a>';
                echo '</div></div>';
            }
        } else {
            echo '<p class="no-articles">No hay artículos disponibles en este momento.</p>';
        }
        ?>
    </div>
</main>

<?php
// Include the footer
include 'includes/footer.php';
?> 
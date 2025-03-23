<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Get faculty slug
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

// If no valid slug, redirect to homepage
if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Set current page for navigation highlight
$current_page = $slug;

include 'includes/header.php';

// Get faculty info
$faculty_query = "SELECT * FROM faculties WHERE slug = ?";
$faculty_stmt = $conn->prepare($faculty_query);
$faculty_stmt->bind_param("s", $slug);
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();

// If faculty doesn't exist, redirect to homepage
if ($faculty_result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$faculty = $faculty_result->fetch_assoc();
$page_title = $faculty['name'];

// Get categories in this faculty
$categories_query = "SELECT * FROM categories WHERE faculty_id = ? ORDER BY name";
$categories_stmt = $conn->prepare($categories_query);
$categories_stmt->bind_param("i", $faculty['id']);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();

// Get all articles from this faculty's categories
$articles_query = "SELECT DISTINCT a.*, GROUP_CONCAT(c.name) as category_names 
                  FROM articles a 
                  JOIN article_categories ac ON a.id = ac.article_id
                  JOIN categories c ON ac.category_slug = c.slug 
                  WHERE c.faculty_id = ?
                  GROUP BY a.id
                  ORDER BY a.date_published DESC";
$articles_stmt = $conn->prepare($articles_query);
$articles_stmt->bind_param("i", $faculty['id']);
$articles_stmt->execute();
$articles_result = $articles_stmt->get_result();
?>

<section class="hero faculty-hero">
    <div class="container">
        <h1><?= htmlspecialchars($faculty['name']) ?></h1>
        <?php if (!empty($faculty['description'])): ?>
            <p class="faculty-description"><?= htmlspecialchars($faculty['description']) ?></p>
        <?php endif; ?>
        
        <?php if ($categories_result->num_rows > 0): ?>
        <section class="categories-section">
            <h2>Categorías</h2>
            <div class="categories-grid">
                <?php while ($cat = $categories_result->fetch_assoc()): 
                    // Get article count for this category
                    $count_query = "SELECT COUNT(DISTINCT a.id) as count 
                                  FROM articles a 
                                  JOIN article_categories ac ON a.id = ac.article_id 
                                  WHERE ac.category_slug = ?";
                    $count_stmt = $conn->prepare($count_query);
                    $count_stmt->bind_param("s", $cat['slug']);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $article_count = $count_result->fetch_assoc()['count'];
                    $count_stmt->close();
                ?>
                <div class="category-item">
                    <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                        <span class="category-count"><?= $article_count ?> artículo<?= $article_count != 1 ? 's' : '' ?></span>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</section>

<main class="container">
    <div class="news-grid">
        <?php if ($articles_result->num_rows > 0): ?>
            <?php while ($article = $articles_result->fetch_assoc()): ?>
                <?php
                $date = new DateTime($article['date_published']);
                $formatted_date = $date->format('F j, Y');
                $has_video = !empty($article['video_url']);
                ?>
                <div class="news-card">
                    <?php if (!empty($article['image_url'])): ?>
                        <?php if ($has_video): ?>
                            <div class="video-container">
                                <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($article['title']) ?>">
                            </div>
                        <?php else: ?>
                            <div class="news-image">
                                <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($article['title']) ?>">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <h2><?= htmlspecialchars($article['title']) ?></h2>
                        <div class="metadata">
                            <span class="date"><?= $formatted_date ?></span>
                            <span class="categories"><?= htmlspecialchars($article['category_names']) ?></span>
                        </div>
                        <p><?= htmlspecialchars($article['summary']) ?></p>
                        <a href="article.php?id=<?= $article['id'] ?>" class="read-more">
                            <?= $has_video ? 'Watch Video' : 'Read More' ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-articles">No articles found in this faculty.</p>
        <?php endif; ?>
    </div>
</main>

<?php
$faculty_stmt->close();
$categories_stmt->close();
$articles_stmt->close();
include 'includes/footer.php';
?> 
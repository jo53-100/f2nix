<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Get category slug
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

// If no valid slug, redirect to homepage
if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Set current page for navigation highlight
$current_page = $slug;

include 'includes/header.php';

// Get category info
$cat_query = "SELECT * FROM categories WHERE slug = ?";
$cat_stmt = $conn->prepare($cat_query);
$cat_stmt->bind_param("s", $slug);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();

// If category doesn't exist, redirect to homepage
if ($cat_result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$category = $cat_result->fetch_assoc();
$page_title = $category['name'] . ' News';

// Get subcategories if they exist
$subcategories_query = "SELECT * FROM categories WHERE parent_id = ? ORDER BY name";
$subcategories_stmt = $conn->prepare($subcategories_query);
$subcategories_stmt->bind_param("i", $category['id']);
$subcategories_stmt->execute();
$subcategories_result = $subcategories_stmt->get_result();

// Get articles in this category and its subcategories
$articles_query = "SELECT DISTINCT a.* FROM articles a 
                  JOIN article_categories ac ON a.id = ac.article_id
                  JOIN categories c ON ac.category_slug = c.slug 
                  WHERE c.slug = ? OR c.parent_id = ? 
                  ORDER BY a.date_published DESC";
$articles_stmt = $conn->prepare($articles_query);
$articles_stmt->bind_param("si", $slug, $category['id']);
$articles_stmt->execute();
$articles_result = $articles_stmt->get_result();
?>

<section class="hero">
    <div class="container">
        <h1><?= htmlspecialchars($category['name']) ?></h1>
        <?php if ($subcategories_result->num_rows > 0): ?>
        <div class="subcategories">
            <ul>
                <?php while ($subcat = $subcategories_result->fetch_assoc()): ?>
                <li>
                    <a href="<?= SITE_URL ?>/category.php?slug=<?= htmlspecialchars($subcat['slug']) ?>"
                       class="<?= isset($_GET['subcategory']) && $_GET['subcategory'] == $subcat['slug'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($subcat['name']) ?>
                    </a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
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
                        <div class="date"><?= $formatted_date ?></div>
                        <p><?= htmlspecialchars($article['summary']) ?></p>
                        <a href="article.php?id=<?= $article['id'] ?>" class="read-more">
                            <?= $has_video ? 'Watch Video' : 'Read More' ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-articles">No articles found in this category.</p>
        <?php endif; ?>
    </div>
</main>

<?php
$cat_stmt->close();
$articles_stmt->close();
include 'includes/footer.php';
?>
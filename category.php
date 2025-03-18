<?php
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

// Get articles in this category
$articles_query = "SELECT * FROM articles WHERE category = ? ORDER BY date_published DESC";
$articles_stmt = $conn->prepare($articles_query);
$articles_stmt->bind_param("s", $slug);
$articles_stmt->execute();
$articles_result = $articles_stmt->get_result();
?>

<section class="hero">
    <div class="container">
        <h1><?= htmlspecialchars($category['name']) ?> News</h1>
        <p>Latest updates from the <?= htmlspecialchars($category['name']) ?> category</p>
    </div>
</section>

<main class="container">
    <div class="news-grid">
        <?php if ($articles_result->num_rows > 0): ?>
            <?php while ($article = $articles_result->fetch_assoc()): ?>
                <div class="news-card">
                    <?php if (!empty($article['image_url'])): ?>
                        <div class="news-image">
                            <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                        </div>
                    <?php elseif (!empty($article['video_url'])): ?>
                        <div class="video-container">
                            <img src="assets/images/video-thumbnail.jpg" alt="Video thumbnail">
                        </div>
                    <?php endif; ?>

                    <div class="news-content">
                        <h3><?= htmlspecialchars($article['title']) ?></h3>
                        <div class="date"><?= format_date($article['date_published']) ?></div>
                        <p><?= htmlspecialchars($article['summary']) ?></p>
                        <a href="article.php?id=<?= $article['id'] ?>" class="read-more">Read More</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-articles">
                <p>No articles found in this category.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
$cat_stmt->close();
$articles_stmt->close();
include 'includes/footer.php';
?>
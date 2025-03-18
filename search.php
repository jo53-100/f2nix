<?php
$current_page = 'search';
$search_term = isset($_GET['q']) ? clean_input($_GET['q']) : '';
$page_title = 'Search Results';

include 'includes/header.php';

// Search query
$query = "SELECT a.*, c.name as category_name
          FROM articles a
          JOIN categories c ON a.category = c.slug
          WHERE a.title LIKE ? OR a.content LIKE ? OR a.summary LIKE ?
          ORDER BY a.date_published DESC";
$search_param = "%$search_term%";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="hero">
    <div class="container">
        <h1>Search Results</h1>
        <p>Results for: <?= htmlspecialchars($search_term) ?></p>
    </div>
</section>

<main class="container">
    <div class="search-form">
        <form action="search.php" method="GET">
            <input type="text" name="q" value="<?= htmlspecialchars($search_term) ?>" placeholder="Search articles...">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="news-grid">
        <?php if($result->num_rows > 0): ?>
            <?php while($article = $result->fetch_assoc()): ?>
                <div class="news-card">
                    <?php if(!empty($article['image_url'])): ?>
                        <div class="news-image">
                            <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                        </div>
                    <?php elseif(!empty($article['video_url'])): ?>
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
            <div class="no-results">
                <p>No results found for "<?= htmlspecialchars($search_term) ?>".</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
$stmt->close();
include 'includes/footer.php';
?>
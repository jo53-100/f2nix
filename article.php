<?php
// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no valid ID, redirect to homepage
if($id <= 0) {
    header('Location: index.php');
    exit;
}

// Set current page for header
$current_page = 'article';
$page_title = 'Article';

include 'includes/config.php';
include 'includes/db_connect.php';

// Fetch article with its categories
$query = "SELECT a.*, 
          GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') as category_names,
          GROUP_CONCAT(DISTINCT c.slug ORDER BY c.name ASC SEPARATOR ', ') as category_slugs,
          f.name as faculty_name, f.slug as faculty_slug
          FROM articles a
          LEFT JOIN article_categories ac ON a.id = ac.article_id
          LEFT JOIN categories c ON ac.category_slug = c.slug
          LEFT JOIN faculties f ON c.faculty_id = f.id
          WHERE a.id = ?
          GROUP BY a.id";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// If article doesn't exist, redirect to homepage
if($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$article = $result->fetch_assoc();
$page_title = $article['title'];

include 'includes/header.php';

// Increase view count
increase_view_count($conn, $id);

// Fetch related articles from the same categories
$related_query = "SELECT DISTINCT a.id, a.title, a.image_url
                  FROM articles a
                  JOIN article_categories ac1 ON a.id = ac1.article_id
                  JOIN article_categories ac2 ON ac1.category_slug = ac2.category_slug
                  WHERE ac2.article_id = ? AND a.id != ?
                  ORDER BY a.date_published DESC
                  LIMIT 3";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("ii", $id, $id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>

<main class="container article-page">
    <article class="single-article">
        <header class="article-header">
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            <div class="article-meta">
                <span class="date"><?= date('F j, Y', strtotime($article['date_published'])) ?></span>
                <?php if(!empty($article['author'])): ?>
                    <span class="author">Por <?= htmlspecialchars($article['author']) ?></span>
                <?php endif; ?>
                <?php if(!empty($article['category_names'])): ?>
                    <?php 
                    $categories = explode(', ', $article['category_names']);
                    $slugs = explode(', ', $article['category_slugs']);
                    ?>
                    <div class="article-categories">
                        <?php foreach($categories as $index => $category): ?>
                            <a href="category.php?slug=<?= htmlspecialchars($slugs[$index]) ?>" class="category-badge">
                                <?= htmlspecialchars($category) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if(!empty($article['faculty_name'])): ?>
                    <span class="faculty">
                        <a href="faculty.php?slug=<?= htmlspecialchars($article['faculty_slug']) ?>">
                            <?= htmlspecialchars($article['faculty_name']) ?>
                        </a>
                    </span>
                <?php endif; ?>
            </div>
        </header>

        <?php if(!empty($article['image_url'])): ?>
            <div class="article-image-container">
                <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="responsive-image">
            </div>
        <?php endif; ?>

        <?php if(!empty($article['video_url'])): ?>
            <?php if(strpos($article['video_url'], 'instagram.com') !== false): ?>
                <div class="instagram-container">
                    <iframe 
                        src="<?= htmlspecialchars($article['video_url']) ?>" 
                        frameborder="0"
                        scrolling="no"
                        allowtransparency="true"
                        allowfullscreen="true"
                        width="100%"
                        height="100%"
                        style="background: white;">
                    </iframe>
                </div>
            <?php else: ?>
                <div class="video-container">
                    <iframe 
                        src="<?= htmlspecialchars($article['video_url']) ?>" 
                        frameborder="0" 
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        width="100%"
                        height="100%">
                    </iframe>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(!empty($article['pdf_url'])): ?>
            <div class="pdf-container">
                <h3>Documento PDF</h3>
                <div class="pdf-viewer">
                    <object data="<?= htmlspecialchars($article['pdf_url']) ?>" type="application/pdf" width="100%" height="600px">
                        <p>Tu navegador no puede mostrar el PDF directamente. 
                           <a href="<?= htmlspecialchars($article['pdf_url']) ?>" target="_blank">Haz clic aquí para descargar el PDF</a>
                        </p>
                    </object>
                </div>
            </div>
        <?php endif; ?>

        <div class="article-content">
            <?= nl2br(htmlspecialchars($article['content'])) ?>
        </div>
    </article>

</main>

<?php
// Close database statements
$stmt->close();
$related_stmt->close();

// Include the footer
include 'includes/footer.php';
?>

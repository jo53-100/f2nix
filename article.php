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

// Fetch article
$query = "SELECT a.*, c.name as category_name
          FROM articles a
          LEFT JOIN categories c ON a.category = c.slug
          WHERE a.id = ?";
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

// Fetch related articles
$related_query = "SELECT id, title, image_url
                  FROM articles
                  WHERE category = ? AND id != ?
                  ORDER BY date_published DESC
                  LIMIT 3";
$related_stmt = $conn->prepare($related_query);
$related_stmt->bind_param("si", $article['category'], $id);
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
                <span class="category">
                    <a href="category.php?slug=<?= $article['category'] ?>">
                        <?= htmlspecialchars($article['category_name']) ?>
                    </a>
                </span>
            </div>
        </header>

        <?php if(!empty($article['image_url'])): ?>
            <div class="article-image-container">
                <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="responsive-image">
            </div>
        <?php endif; ?>

        <?php if(!empty($article['video_url'])): ?>
            <div class="video-container">
                <iframe src="<?= htmlspecialchars($article['video_url']) ?>" frameborder="0" allowfullscreen></iframe>
            </div>
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

<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$page_title = 'Facultades';
include 'includes/header.php';

// Get all faculties
$query = "SELECT f.*, COUNT(DISTINCT a.id) as article_count 
          FROM faculties f
          LEFT JOIN categories c ON f.id = c.faculty_id
          LEFT JOIN article_categories ac ON c.slug = ac.category_slug
          LEFT JOIN articles a ON ac.article_id = a.id
          GROUP BY f.id
          ORDER BY f.name";
$result = $conn->query($query);
?>

<section class="hero">
    <div class="container">
        <h1>Nuestras Facultades</h1>
        <p class="subtitle">Explora las noticias y documentos de cada facultad</p>
    </div>
</section>

<main class="container">
    <div class="faculties-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($faculty = $result->fetch_assoc()): ?>
                <div class="faculty-card">
                    <h2><?= htmlspecialchars($faculty['name']) ?></h2>
                    <?php if (!empty($faculty['description'])): ?>
                        <p><?= htmlspecialchars($faculty['description']) ?></p>
                    <?php endif; ?>
                    <div class="faculty-meta">
                        <span class="article-count"><?= $faculty['article_count'] ?> artículos</span>
                    </div>
                    <a href="faculty.php?slug=<?= htmlspecialchars($faculty['slug']) ?>" class="faculty-link">
                        Ver noticias y documentos
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-faculties">No hay facultades disponibles.</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?> 
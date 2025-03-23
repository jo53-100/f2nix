<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!is_logged_in()) {
    header('Location: lo2in.php');
    exit;
}

// Get articles
$query = "SELECT a.*, GROUP_CONCAT(c.name) as category_name
          FROM articles a
          LEFT JOIN article_categories ac ON a.id = ac.article_id
          LEFT JOIN categories c ON ac.category_slug = c.slug
          GROUP BY a.id
          ORDER BY a.date_published DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Proyecto Fenix</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Fenix Admin</div>
                <nav>
                    <ul>
                        <li><a href="../index.php">View Site</a></li>
                        <li><a href="add_article.php">Add Article</a></li>
                        <li><a href="manage_categories.php">Manage Categories</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container admin-content">
        <h1>Articles Management</h1>

        <div class="admin-actions">
            <a href="add_article.php" class="btn">Add New Article</a>
        </div>

        <table class="articles-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Featured</th>
                    <th>Views</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($article = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $article['id'] ?></td>
                            <td><?= htmlspecialchars($article['title']) ?></td>
                            <td><?= htmlspecialchars($article['category_name']) ?></td>
                            <td><?= format_date($article['date_published']) ?></td>
                            <td><?= $article['featured'] ? 'Yes' : 'No' ?></td>
                            <td><?= $article['views'] ?></td>
                            <td class="actions">
                                <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn small">Edit</a>
                                <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn small danger" onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No articles found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Proyecto Fenix Admin. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: lo2in.php');
    exit;
}

// Get categories
$categories = get_categories($conn);

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $summary = clean_input($_POST['summary']);
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $author = clean_input($_POST['author']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle PDF upload
    $pdf_url = '';
    if ($_FILES['pdf']['size'] > 0) {
        $target_dir = "../uploads/pdfs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Check file size and type
        if ($_FILES["pdf"]["size"] > 10000000) { // 10MB limit
            $error = "PDF file is too large (max 10MB).";
        } elseif ($file_extension != "pdf") {
            $error = "Only PDF files are allowed.";
        } elseif (move_uploaded_file($_FILES["pdf"]["tmp_name"], $target_file)) {
            $pdf_url = "uploads/pdfs/" . $new_filename;
        } else {
            $error = "Error uploading PDF file.";
        }
    }

    // Handle file upload for image
    $image_url = '';
    if ($_FILES['image']['size'] > 0) {
        $target_dir = "../uploads/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Check file size and type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_mime_type = mime_content_type($_FILES['image']['tmp_name']);

        if ($_FILES["image"]["size"] > 5000000) {
            $error = "Image file is too large (max 5MB).";
        } elseif (!in_array($file_extension, $allowed_types) || !in_array($file_mime_type, $allowed_mime_types)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "uploads/" . $new_filename;
        } else {
            $error = "Error uploading image file.";
        }
    }

    // Handle video URL
    $video_url = clean_input($_POST['video_url']);
    if (!empty($video_url)) {
        // Handle YouTube URLs
        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\s*(?:\w*\/)*(?:v\/|e(?:mbed)?)\/|\w*\/|watch\?[^&]*v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $matches)) {
            $video_id = $matches[1];
            $video_url = "https://www.youtube.com/embed/" . $video_id;
        }
        // Handle Instagram URLs
        elseif (preg_match('/instagram\.com\/(?:p|reel)\/([A-Za-z0-9_-]+)/', $video_url, $matches)) {
            $post_id = $matches[1];
            $video_url = "https://www.instagram.com/p/" . $post_id . "/embed/captioned/";
        } else {
            $error = "Please enter a valid YouTube or Instagram URL";
        }
    }

    if (empty($title) || empty($content) || empty($summary) || empty($categories)) {
        $error = "Title, content, summary, and at least one category are required.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert article
            $query = "INSERT INTO articles (title, content, summary, image_url, video_url, pdf_url, author, featured)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssi", $title, $content, $summary, $image_url, $video_url, $pdf_url, $author, $featured);
            
            if ($stmt->execute()) {
                $article_id = $conn->insert_id;
                
                // Insert categories
                $cat_query = "INSERT INTO article_categories (article_id, category_slug) VALUES (?, ?)";
                $cat_stmt = $conn->prepare($cat_query);
                
                foreach ($categories as $category_slug) {
                    $cat_stmt->bind_param("is", $article_id, $category_slug);
                    if (!$cat_stmt->execute()) {
                        throw new Exception("Error inserting category: " . $cat_stmt->error);
                    }
                }
                
                $conn->commit();
                $success = "Article added successfully.";
                // Clear form data
                $title = $content = $summary = $author = $video_url = '';
                $featured = 0;
                $categories = [];
            } else {
                throw new Exception("Error inserting article: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Database error: " . $e->getMessage());
            $error = "An error occurred while saving the article. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Admin Panel</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="../index.php">View Site</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container admin-content">
        <h1>Add New Article</h1>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="article-form">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="summary">Summary:</label>
                <textarea id="summary" name="summary" rows="3" required><?= isset($summary) ? htmlspecialchars($summary) : '' ?></textarea>
                <small>Brief description for article listings.</small>
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="10" required><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label>Categories:</label>
                <div class="categories-grid">
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-checkbox">
                            <input type="checkbox" id="cat_<?= $cat['slug'] ?>" 
                                   name="categories[]" value="<?= $cat['slug'] ?>"
                                   <?= (isset($categories) && in_array($cat['slug'], $categories)) ? 'checked' : '' ?>>
                            <label for="cat_<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="author">Author (optional):</label>
                <input type="text" id="author" name="author" value="<?= isset($author) ? htmlspecialchars($author) : '' ?>">
            </div>

            <div class="form-group">
                <label for="image">Image (optional):</label>
                <input type="file" id="image" name="image">
                <small>Max size: 5MB. Allowed types: JPG, JPEG, PNG, GIF.</small>
            </div>

            <div class="form-group">
                <label for="video_url">Video/Social Media URL (optional):</label>
                <input type="url" id="video_url" name="video_url" value="<?= isset($video_url) ? htmlspecialchars($video_url) : '' ?>">
                <small>Enter a YouTube URL or Instagram post/reel URL</small>
            </div>

            <div class="form-group">
                <label for="pdf">PDF Document (optional):</label>
                <input type="file" id="pdf" name="pdf" accept=".pdf">
                <small>Max size: 10MB. Only PDF files allowed.</small>
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="featured" name="featured" <?= (isset($featured) && $featured) ? 'checked' : '' ?>>
                <label for="featured">Featured Article</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Add Article</button>
                <a href="index.php" class="btn secondary">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
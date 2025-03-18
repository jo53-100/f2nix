<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!is_logged_in()) {
    header('Location: lo2in.php');
    exit;
}

// Get article ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no valid ID, redirect to admin dashboard
if($id <= 0) {
    header('Location: index.php');
    exit;
}

// Get categories
$categories = get_categories($conn);

// Get article data
$query = "SELECT * FROM articles WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// If article doesn't exist, redirect to admin dashboard
if($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$article = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = clean_input($_POST['title']);
    $content = clean_input($_POST['content']);
    $summary = clean_input($_POST['summary']);
    $category = clean_input($_POST['category']);
    $author = clean_input($_POST['author']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Use existing image or update if new one provided
    $image_url = $article['image_url'];
    if($_FILES['image']['size'] > 0) {
        $target_dir = "../uploads/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Check file size and type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if($_FILES["image"]["size"] > 5000000) {
            $error = "Image file is too large (max 5MB)";
        } elseif(!in_array($file_extension, $allowed_types)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed";
        } elseif(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image if exists
            if(!empty($image_url) && file_exists("../" . $image_url)) {
                unlink("../" . $image_url);
            }
            $image_url = "uploads/" . $new_filename;
        } else {
            $error = "Error uploading image file";
        }
    }

    // Handle PDF upload
    $pdf_url = $article['pdf_url'];
    if($_FILES['pdf']['size'] > 0) {
        $target_dir = "../uploads/pdfs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if($_FILES["pdf"]["size"] > 10000000) { // 10MB limit
            $error = "PDF file is too large (max 10MB)";
        } elseif($file_extension != "pdf") {
            $error = "Only PDF files are allowed";
        } elseif(move_uploaded_file($_FILES["pdf"]["tmp_name"], $target_file)) {
            // Delete old PDF if exists
            if(!empty($pdf_url) && file_exists("../" . $pdf_url)) {
                unlink("../" . $pdf_url);
            }
            $pdf_url = "uploads/pdfs/" . $new_filename;
        } else {
            $error = "Error uploading PDF file";
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
            // Use oEmbed format
            $video_url = "https://www.instagram.com/p/" . $post_id . "/embed/captioned/";
        } else {
            $error = "Please enter a valid YouTube or Instagram URL";
        }
    }

    if(empty($title) || empty($content) || empty($summary) || empty($category)) {
        $error = "Title, content, summary, and category are required";
    } else {
        // Update article
        $query = "UPDATE articles SET
                  title = ?,
                  content = ?,
                  summary = ?,
                  image_url = ?,
                  video_url = ?,
                  pdf_url = ?,
                  category = ?,
                  author = ?,
                  featured = ?
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssii", $title, $content, $summary, $image_url, $video_url, $pdf_url, $category, $author, $featured, $id);

        if($stmt->execute()) {
            $success = "Article updated successfully";
            // Refresh article data
            $article['title'] = $title;
            $article['content'] = $content;
            $article['summary'] = $summary;
            $article['image_url'] = $image_url;
            $article['video_url'] = $video_url;
            $article['pdf_url'] = $pdf_url;
            $article['category'] = $category;
            $article['author'] = $author;
            $article['featured'] = $featured;
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - University News</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">University News Admin</div>
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
        <h1>Edit Article</h1>

        <?php if(!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="article-form">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="summary">Summary (brief description for article listings):</label>
                <textarea id="summary" name="summary" rows="3" required><?= htmlspecialchars($article['summary']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="10" required><?= htmlspecialchars($article['content']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['slug'] ?>" <?= ($article['category'] === $cat['slug']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="author">Author (optional):</label>
                <input type="text" id="author" name="author" value="<?= htmlspecialchars($article['author']) ?>">
            </div>

            <?php if(!empty($article['image_url'])): ?>
                <div class="form-group">
                    <label>Current Image:</label>
                    <div class="current-image">
                        <img src="../<?= htmlspecialchars($article['image_url']) ?>" alt="Current Image" style="max-width: 200px;">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="image">New Image (optional):</label>
                <input type="file" id="image" name="image">
                <small>Max size: 5MB. Allowed types: JPG, JPEG, PNG, GIF</small>
            </div>

            <?php if(!empty($article['pdf_url'])): ?>
                <div class="form-group">
                    <label>Current PDF:</label>
                    <div class="current-pdf">
                        <a href="../<?= htmlspecialchars($article['pdf_url']) ?>" target="_blank" class="btn btn-small">View Current PDF</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="pdf">New PDF Document (optional):</label>
                <input type="file" id="pdf" name="pdf" accept=".pdf">
                <small>Max size: 10MB. Only PDF files allowed.</small>
            </div>

            <div class="form-group">
                <label for="video_url">Video/Social Media URL (optional):</label>
                <input type="url" id="video_url" name="video_url" value="<?= htmlspecialchars($article['video_url']) ?>">
                <small>Enter a YouTube URL (e.g., youtube.com/watch?v=xxxxx) or Instagram post/reel URL (e.g., instagram.com/p/xxxxx or instagram.com/reel/xxxxx)</small>
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="featured" name="featured" <?= $article['featured'] ? 'checked' : '' ?>>
                <label for="featured">Featured Article</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Update Article</button>
                <a href="index.php" class="btn secondary">Cancel</a>
            </div>
        </form>
    </main>

    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> University News Admin. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
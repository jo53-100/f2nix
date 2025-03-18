<?php require_once '../includes/config.php';
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

// Get article image URL first (for file deletion)
$query = "SELECT image_url FROM articles WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $article = $result->fetch_assoc();

    // Delete associated image file if exists
    if(!empty($article['image_url']) && file_exists("../uploads/" . $article['image_url'])) {
        unlink("../uploads/" . $article['image_url']);
    }

    // Delete the article from database
    $delete_query = "DELETE FROM articles WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $id);

    if($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Article deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting article: " . $conn->error;
    }

    $delete_stmt->close();
} else {
    $_SESSION['error_message'] = "Article not found.";
}

$stmt->close();
$conn->close();

// Redirect back to articles list
header('Location: manage_articles.php');
exit;
?>
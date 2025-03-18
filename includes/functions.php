<?php
// Clean user input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format date
function format_date($date) {
    return date('F j, Y', strtotime($date));
}

// Create URL slug
function create_slug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $slug = strtolower($slug);
    $slug = trim($slug, '-');
    return $slug;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Get all categories
function get_categories($conn) {
    $query = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($query);
    $categories = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}

// Increase article view count
function increase_view_count($conn, $article_id) {
    $query = "UPDATE articles SET views = views + 1 WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $stmt->close();
}
?>
<?php
// Database connection
include 'includes/config.php';

// Set current page
$current_page = 'home';

// Get recent articles - make sure this matches your table structure
$query = "SELECT id, title, image_url, video_url, date_published, summary, category 
          FROM articles 
          ORDER BY date_published DESC 
          LIMIT 6";
$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Load the HTML file content
$html_content = file_get_contents('index.html');

// Generate dynamic news cards HTML
$news_cards = '';
if (mysqli_num_rows($result) > 0) {
    while ($article = mysqli_fetch_assoc($result)) {
        // Format date
        $date = new DateTime($article['date_published']);
        $formatted_date = $date->format('F j, Y');
        
        // Determine if it has video
        $has_video = !empty($article['video_url']);
        
        // Start news card
        $news_cards .= '<div class="news-card">';
        
        // Add image or video container
        if (!empty($article['image_url'])) {
            if ($has_video) {
                $news_cards .= '<div class="video-container">';
                $news_cards .= '<img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '">';
                $news_cards .= '</div>';
            } else {
                $news_cards .= '<div class="news-image">';
                $news_cards .= '<img src="' . htmlspecialchars($article['image_url']) . '" alt="' . htmlspecialchars($article['title']) . '">';
                $news_cards .= '</div>';
            }
        }
        
        // Add content
        $news_cards .= '<div class="news-content">';
        $news_cards .= '<h2>' . htmlspecialchars($article['title']) . '</h2>';
        $news_cards .= '<div class="date">' . $formatted_date . '</div>';
        $news_cards .= '<p>' . htmlspecialchars($article['summary']) . '</p>';
        
        $link_text = $has_video ? 'Watch Video' : 'Read More';
        $news_cards .= '<a href="article.php?id=' . $article['id'] . '" class="read-more">' . $link_text . '</a>';
        $news_cards .= '</div></div>';
    }
} else {
    $news_cards = '<p>No articles found. Please add some articles to your database.</p>';
}

// Find the news-grid div and replace its content
$start_marker = '<div class="news-grid">';
$end_marker = '</div>';

$start_pos = strpos($html_content, $start_marker);
$end_pos = strpos($html_content, $end_marker, $start_pos);

if ($start_pos !== false && $end_pos !== false) {
    $before_grid = substr($html_content, 0, $start_pos + strlen($start_marker));
    $after_grid = substr($html_content, $end_pos);
    
    // Combine the parts with our dynamic content
    $html_content = $before_grid . "\n" . $news_cards . "\n" . $after_grid;
} else {
    die("Could not find the news grid section in the HTML file.");
}

// Fix the navigation active class
$html_content = str_replace('class="active"', '', $html_content);
$html_content = str_replace('<a href="#">Home</a>', '<a href="#" class="active">Home</a>', $html_content);

// Update links to proper pages
$html_content = str_replace('<a href="#" class="active">Home</a>', '<a href="ind3x.php" class="active">Home</a>', $html_content);
$html_content = str_replace('<a href="#">Campus</a>', '<a href="category.php?slug=campus">Campus</a>', $html_content);
$html_content = str_replace('<a href="#">Academic</a>', '<a href="category.php?slug=academic">Academic</a>', $html_content);
$html_content = str_replace('<a href="#">Sports</a>', '<a href="category.php?slug=sports">Sports</a>', $html_content);
$html_content = str_replace('<a href="#">Events</a>', '<a href="category.php?slug=events">Events</a>', $html_content);

// Output the final HTML
echo $html_content;
?>
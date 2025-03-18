<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/db_connect.php'; ?>
<?php require_once 'includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Proyecto Fenix</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</head>
<body>
    <?php
    // Get the current page name for active state
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Proyecto Fenix</div>
                <nav>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/index.php" <?= $current_page == 'index.php' ? 'class="active"' : '' ?>>Noticias</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php" <?= $current_page == 'about' ? 'class="active"' : '' ?>>¿Quiénes somos?</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
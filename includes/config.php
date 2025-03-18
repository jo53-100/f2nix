<?php
// Start session
session_start();

// Site configuration
define('SITE_NAME', 'Proyecto Fenix');
define('SITE_URL', 'http://localhost/proyectof2nix');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'proyectof2nix');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/proyectof2nix/uploads/');
?>
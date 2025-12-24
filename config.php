<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'newspaper_db');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Website configuration
define('SITE_TITLE', 'RECYCLEZONE NEWSPAPER');
define('SITE_URL', 'http://localhost/newspaper');

// Add to config.php after database connection
function get_image_url($path) {
    if(empty($path)) {
        return 'https://via.placeholder.com/800x400?text=No+Image';
    }
    
    if(filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }
    
    // Check if file exists locally
    if(file_exists($path)) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
        return $base_url . $path;
    } elseif(file_exists('../' . $path)) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/';
        return $base_url . $path;
    }
    
    return 'https://via.placeholder.com/800x400?text=Image+Not+Found';
}

?>
<?php
// config.php - Database Configuration

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your database username
define('DB_PASS', ''); // Change this to your database password
define('DB_NAME', 'recipe_competition_db'); // Change this to your database name

// Establish database connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set character set to utf8mb4 for proper encoding
$mysqli->set_charset("utf8mb4");
?>

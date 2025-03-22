<?php
require_once __DIR__ . '/db.php'; // Ensure the database connection is included
require_once 'controllers/AuthController.php';

// Instantiate AuthController with the database connection
$auth = new AuthController($db);
$auth->logout();

// Redirect to login page after logout
header("Location: login.php");
exit();
?>

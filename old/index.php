<?php
// index.php - Landing Page
require_once 'db.php'; // Ensure database connection is included
require_once 'controllers/AuthController.php';

$auth = new AuthController($db); // Pass $db as an argument
$isLoggedIn = $auth->isAuthenticated();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Competition</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Welcome to Recipe Competition</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="competitions.php">Competitions</a></li>
                <li><a href="community.php">Community</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="intro">
            <h2>Join the Culinary Adventure</h2>
            <p>Share your best recipes, compete in exciting challenges, and connect with food lovers around the world.</p>
            <?php if (!$isLoggedIn): ?>
                <a href="register.php" class="btn">Get Started</a>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Recipe Competition. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
// dashboard.php - User Dashboard
require_once 'controllers/AuthController.php';
require_once 'controllers/UserController.php';
require_once 'db.php'; // Database connection

session_start();

$auth = new AuthController($db);
$userController = new UserController($db);

// Redirect to login if the user is not authenticated
if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's details
$user = $userController->getCurrentUser();
if (!$user) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($user['username']);
$email = htmlspecialchars($user['email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome to Your Dashboard</h1>
        </header>
        
        <div class="profile-section">
            <h2>Welcome, <?php echo $username; ?>!</h2>
            <p>Email: <?php echo $email; ?></p>
            <a href="edit_profile.php" class="btn">Edit Profile</a>
        </div>
        
        <section class="dashboard-menu">
            <nav>
                <ul>
                    <li><a href="views/submit_recipe.php">Submit Recipe</a></li>
                    <li><a href="views/my_recipes.php">My Recipes</a></li>
                    <li><a href="views/create_competition.php">Start Competition</a></li>
                    <li><a href="views/competitions.php">View Competitions</a></li>
                    <li><a href="views/community.php">Community</a></li>
                    <li><a href="logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </section>
    </div>
</body>
</html>
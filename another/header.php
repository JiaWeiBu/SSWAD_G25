<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "culinary_db");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Get current username if logged in
function getCurrentUsername()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : "Guest";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .feature-box {
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            min-height: 150px;
        }

        /* Sticky footer styles */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            min-height: 100vh;
            /* Use viewport height */
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto;
            /* Grow to fill space, but don't shrink */
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        /* Remove extra margins from Bootstrap container if needed */
        .container {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">Recipe App</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="recipes.php">Recipes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="meal_planning.php">Meal Planning</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="community.php">Community</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="competitions.php">Competitions</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <span class="nav-link">Welcome, <?php echo getCurrentUsername(); ?></span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="content">
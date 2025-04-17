<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$plan_id = $_GET['plan_id'] ?? '';

if (empty($plan_id)) {
    header("Location: view_meal_plans.php?error=" . urlencode("No meal plan specified."));
    exit();
}

// Fetch meal plan details
try {
    $stmt = $conn->prepare("
        SELECT 
            mp.plan_id,
            mp.meal_date,
            mp.meal_type,
            mp.recipe_id,
            mp.custom_meal_name,
            mp.custom_meal_ingredients,
            mp.notes,
            r.name AS recipe_name
        FROM meal_plans mp
        LEFT JOIN recipes r ON mp.recipe_id = r.id
        WHERE mp.plan_id = ? AND mp.user_id = ?
    ");
    $stmt->execute([$plan_id, $user_id]);
    $meal_plan = $stmt->fetch();

    if (!$meal_plan) {
        header("Location: view_meal_plans.php?error=" . urlencode("Meal plan not found or you don't have permission to view it."));
        exit();
    }

    // Fetch ingredients and steps if it's a recipe-based meal
    $ingredients = [];
    $steps = [];
    if ($meal_plan['recipe_id']) {
        // Fetch ingredients
        $stmt = $conn->prepare("
            SELECT name, quantity
            FROM ingredients
            WHERE recipe_id = ?
        ");
        $stmt->execute([$meal_plan['recipe_id']]);
        $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch steps
        $stmt = $conn->prepare("
            SELECT step_number, description
            FROM steps
            WHERE recipe_id = ?
            ORDER BY step_number ASC
        ");
        $stmt->execute([$meal_plan['recipe_id']]);
        $steps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    header("Location: view_meal_plans.php?error=" . urlencode("Error fetching meal plan: " . $e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Meal Details - Recipe App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="meal_style.css">
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
                            <a class="nav-link active" href="view_meal_plans.php">View Meal Plan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="community.php">Community</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="competitions.php">Competitions</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <span class="nav-link">Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span>
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
            <main>
                <h2>Meal Plan Details</h2>
                <div class="detail-section">
                    <h3>Basic Information</h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($meal_plan['meal_date']); ?></p>
                    <p><strong>Meal Type:</strong> <?php echo htmlspecialchars($meal_plan['meal_type']); ?></p>
                    <p><strong>Meal Name:</strong> 
                        <?php 
                        if ($meal_plan['recipe_id'] && $meal_plan['recipe_name']) {
                            echo htmlspecialchars($meal_plan['recipe_name']);
                        } elseif ($meal_plan['custom_meal_name']) {
                            echo htmlspecialchars($meal_plan['custom_meal_name']);
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </p>
                </div>

                <div class="detail-section">
                    <h3>Ingredients</h3>
                    <?php if ($meal_plan['recipe_id'] && !empty($ingredients)): ?>
                        <ul>
                            <?php foreach ($ingredients as $ingredient): ?>
                                <li>
                                    <?php 
                                    echo htmlspecialchars($ingredient['name']);
                                    if ($ingredient['quantity']) {
                                        echo " (" . htmlspecialchars($ingredient['quantity']) . ")";
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif ($meal_plan['custom_meal_ingredients']): ?>
                        <p><?php echo htmlspecialchars($meal_plan['custom_meal_ingredients']); ?></p>
                    <?php else: ?>
                        <p>No ingredients available.</p>
                    <?php endif; ?>
                </div>

                <div class="detail-section">
                    <h3>Cooking Steps</h3>
                    <?php if ($meal_plan['recipe_id'] && !empty($steps)): ?>
                        <ol>
                            <?php foreach ($steps as $step): ?>
                                <li><?php echo htmlspecialchars($step['description']); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p>No cooking steps available. This might be a custom meal without predefined steps.</p>
                    <?php endif; ?>
                </div>

                <div class="detail-section">
                    <h3>Notes</h3>
                    <p><?php echo htmlspecialchars($meal_plan['notes'] ?: 'No notes provided.'); ?></p>
                </div>

                <p><a href="view_meal_plans.php">Back to Meal Plans</a></p>
            </main>
        </div>
    </div>
    <footer class="bg-dark text-white py-2">
        <div class="container text-center">
            <p>© 2025 Recipe App | UCCD3243 Server-Side Web Applications Development</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
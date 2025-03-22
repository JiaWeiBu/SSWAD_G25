<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/RecipeController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$recipeController = new RecipeController($db);

if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $ingredients = $_POST['ingredients'] ?? '';

    if ($recipeController->createRecipe($title, $description, $ingredients)) {
        header("Location: recipes.php");
        exit();
    } else {
        $error = "Failed to create recipe. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Recipe</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Create a New Recipe</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="ingredients">Ingredients:</label>
            <textarea id="ingredients" name="ingredients" required></textarea>

            <button type="submit">Create Recipe</button>
        </form>
        <a href="recipes.php">Back to Recipes</a>
    </div>
</body>
</html>

<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/EntryController.php';
require_once __DIR__ . '/../controllers/RecipeController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$entryController = new EntryController($db);
$recipeController = new RecipeController($db);

if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$userId = $auth->getUserId();
$competitionId = $_GET['competition_id'] ?? null;
$recipes = $recipeController->getRecipesByUserId($userId);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = $_POST['recipe_id'] ?? '';
    
    if ($entryController->submitEntry($userId, $competitionId, $recipeId)) {
        header("Location: competition_details.php?id=$competitionId");
        exit();
    } else {
        $error = "Failed to submit entry. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Entry</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Submit Your Recipe</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="">
            <label for="recipe_id">Select Recipe:</label>
            <select id="recipe_id" name="recipe_id" required>
                <option value="">-- Choose a Recipe --</option>
                <?php foreach ($recipes as $recipe): ?>
                    <option value="<?php echo $recipe['recipe_id']; ?>">
                        <?php echo htmlspecialchars($recipe['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Submit Entry</button>
        </form>
        <a href="competition_details.php?id=<?php echo $competitionId; ?>">Back to Competition</a>
        <a href="create_recipe.php" class="button">Create New Recipe</a>
    </div>
</body>
</html>
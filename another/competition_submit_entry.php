<?php
require_once 'db.php';
include 'header.php'; // Include header for navigation and session handling

$competitionId = $_GET['competition_id'] ?? null;

if (!$competitionId) {
    echo "<div class='container mt-3'><p class='text-danger'>Competition not found.</p></div>";
    include 'footer.php';
    exit();
}

// Fetch user's recipes
$userId = $_SESSION['user_id'] ?? null; // Use session to get the logged-in user ID

$stmt = $db->prepare("SELECT id AS recipe_id, name AS title FROM recipes WHERE created_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$recipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$userId) {
        echo "<div class='container mt-3'><p class='text-danger'>You must be logged in to submit an entry.</p></div>";
        include 'footer.php';
        exit();
    }

    $recipeId = $_POST['recipe_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $submission = $_POST['submission'] ?? '';

    if (empty($title) || empty($description) || empty($submission)) {
        $error = "All fields are required.";
    } else {
        $stmt = $db->prepare("INSERT INTO competition_entries (competition_id, user_id, recipe_id, title, description, submission) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $competitionId, $userId, $recipeId, $title, $description, $submission);

        if ($stmt->execute()) {
            header("Location: competition_details.php?id=$competitionId");
            exit();
        } else {
            $error = "Failed to submit entry.";
        }
    }
}
?>

<style>
    .btn-outline-secondary:hover {
        background-color: #0d6efd; /* Fill color on hover */
        color: white; /* White text on hover */
    }
</style>
<div class="container mt-3">
    <!-- Back to Competition Button -->
    <div class="mb-3">
        <a href="competition_details.php?id=<?php echo $competitionId; ?>" class="btn btn-outline-secondary">← Back to Competition</a>
    </div>
    <!-- Submit Recipe Section -->
    <h2>Submit Your Recipe</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if (!$userId): ?>
        <p class="text-danger">You must be logged in to submit an entry.</p>
        <a href="login.php" class="btn btn-primary">Sign In</a>
    <?php elseif (empty($recipes)): ?>
        <p>You don't have any recipes. Please create one to participate in the competition.</p>
        <a href="create_recipe.php" class="btn btn-primary">Create Recipe</a>
    <?php else: ?>
        <form method="post">
            <div class="mb-3">
                <label for="recipe_id" class="form-label">Select Recipe:</label>
                <select name="recipe_id" id="recipe_id" class="form-select" required>
                    <?php foreach ($recipes as $recipe): ?>
                        <option value="<?php echo $recipe['recipe_id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title:</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="submission" class="form-label">Submission:</label>
                <textarea name="submission" id="submission" class="form-control" rows="5" required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    <?php endif; ?>
</div>

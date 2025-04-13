<?php
/**
 * This file allows users to edit their competition entries.
 * It validates user input and updates the entry in the database.
 * 
 * Key Features:
 * - Fetches entry details and validates user authorization.
 * - Displays a form for editing the entry.
 * - Updates the entry in the database on form submission.
 */

require_once 'db.php';

// Check if a session is already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$entryId = $_GET['id'] ?? null;
$competitionId = $_GET['competition_id'] ?? null;

if (!$entryId || !$competitionId) {
    echo "<div class='container mt-3'><p class='text-danger'>Error: Entry ID or Competition ID is missing in the URL.</p></div>";
    exit();
}

// Fetch entry details
$stmt = $db->prepare("SELECT title, description, submission, recipe_id, user_id FROM competition_entries WHERE id = ?");
$stmt->bind_param("i", $entryId);
$stmt->execute();
$entryDetails = $stmt->get_result()->fetch_assoc();

if (!$entryDetails) {
    echo "<div class='container mt-3'><p class='text-danger'>Error: No entry found for ID $entryId.</p></div>";
    exit();
}

// Check if the logged-in user is the author of the entry
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $entryDetails['user_id']) {
    echo "<div class='container mt-3'><p class='text-danger'>You are not authorized to edit this entry.</p></div>";
    exit();
}

// Fetch user's recipes
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT id AS recipe_id, name AS title FROM recipes WHERE created_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$recipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $submission = $_POST['submission'] ?? '';
    $recipeId = $_POST['recipe_id'] ?? '';

    // Validate input
    if (empty($title) || empty($description) || empty($submission) || empty($recipeId)) {
        echo "<div class='container mt-3'><p class='text-danger'>All fields are required.</p></div>";
    } else {
        // Ensure the selected recipe belongs to the user
        $stmt = $db->prepare("SELECT id FROM recipes WHERE id = ? AND created_by = ?");
        $stmt->bind_param("ii", $recipeId, $userId);
        $stmt->execute();
        $recipe = $stmt->get_result()->fetch_assoc();

        if (!$recipe) {
            echo "<div class='container mt-3'><p class='text-danger'>Invalid recipe selection.</p></div>";
        } else {
            // Update the entry
            $stmt = $db->prepare("UPDATE competition_entries SET title = ?, description = ?, submission = ?, recipe_id = ? WHERE id = ?");
            $stmt->bind_param("sssii", $title, $description, $submission, $recipeId, $entryId);
            $stmt->execute();

            header("Location: competition_entry_details.php?id=$entryId&competition_id=$competitionId");
            exit();
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-3">
    <h2>Edit Competition Entry</h2>
    <form method="post">
        <div class="mb-3">
            <label for="recipe_id" class="form-label">Select Recipe:</label>
            <select name="recipe_id" id="recipe_id" class="form-select" required>
                <?php foreach ($recipes as $recipe): ?>
                    <option value="<?php echo $recipe['recipe_id']; ?>" <?php echo $recipe['recipe_id'] == $entryDetails['recipe_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($recipe['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($entryDetails['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($entryDetails['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="submission" class="form-label">Submission</label>
            <textarea name="submission" id="submission" class="form-control" rows="5" required><?php echo htmlspecialchars($entryDetails['submission']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Entry</button>
        <a href="competition_entry_details.php?id=<?php echo $entryId; ?>&competition_id=<?php echo $competitionId; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

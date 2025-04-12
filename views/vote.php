<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VoteController.php';
require_once __DIR__ . '/../controllers/CompetitionController.php';
require_once __DIR__ . '/../controllers/EntryController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$voteController = new VoteController($db);
$competitionController = new CompetitionController($db);
$entryController = new EntryController($db);

// Check if the user is authenticated
if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

// Retrieve competition entries from the session
$entries = $_SESSION['entries'] ?? [];
unset($_SESSION['entries']); // Clear session data after use

$userId = $auth->getUserId();

// Handle form submission for voting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entry_id'])) {
    $entryId = $_POST['entry_id'];
    if ($voteController->castVote($userId, $entryId)) {
        header("Location: competitions.php");
        exit();
    } else {
        $error = "Failed to submit vote. You may have already voted.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote for Competition Entry</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Vote for a Competition Entry</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        
        <!-- Voting form -->
        <form method="post" action="">
            <label for="entry">Select an Entry:</label>
            <select id="entry" name="entry_id" required>
                <?php foreach ($entries as $entry): ?>
                    <option value="<?php echo $entry['entry_id']; ?>">
                        <?php echo htmlspecialchars($entry['recipe_name']); ?> by <?php echo htmlspecialchars($entry['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Vote</button>
        </form>
        <a href="competitions.php">Back to Competitions</a>
    </div>
</body>
</html>
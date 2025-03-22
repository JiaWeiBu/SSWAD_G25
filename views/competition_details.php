<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CompetitionController.php';
require_once __DIR__ . '/../controllers/EntryController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$competitionController = new CompetitionController($db);
$entryController = new EntryController($db);

if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$competitionId = $_GET['id'] ?? null;

if (!$competitionId || !($competition = $competitionController->getCompetitionById($competitionId))) {
    echo "<p>Competition not found.</p>";
    exit();
}

$entries = $entryController->getEntriesByCompetitionId($competitionId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($competition['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($competition['title']); ?></h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($competition['description']); ?></p>
        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($competition['start_date']); ?></p>
        <p><strong>End Date:</strong> <?php echo htmlspecialchars($competition['end_date']); ?></p>
        
        <h3>Entries</h3>
        <?php if (empty($entries)) : ?>
            <p>No entries yet.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($entries as $entry) : ?>
                    <li>
                        <a href="entry_details.php?id=<?php echo $entry['entry_id']; ?>">
                            <?php echo htmlspecialchars($entry['recipe_name']); ?> by <?php echo htmlspecialchars($entry['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <a href="competitions.php">Back to Competitions</a>
        <a href="submit_entry.php?competition_id=<?php echo $competitionId; ?>" class="button">Submit Your Entry</a>
    </div>
</body>
</html>

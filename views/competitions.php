<?php
require_once __DIR__ . '/../controllers/CompetitionController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$competitionController = new CompetitionController($db);

// Check if the user is authenticated
if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

// Fetch all competitions
$competitions = $competitionController->getAllCompetitions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competitions</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Competitions</h2>
        <a href="create_competition.php" class="btn">Create New Competition</a>
        <ul class="competition-list">
            <?php foreach ($competitions as $competition): ?>
                <li>
                    <h3><?php echo htmlspecialchars($competition['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($competition['description'])); ?></p>
                    <p>Start Date: <?php echo $competition['start_date']; ?> | End Date: <?php echo $competition['end_date']; ?></p>
                    <a href="competition_details.php?id=<?php echo $competition['competition_id']; ?>">View Details</a>

                    <!-- Show edit and delete options for admins or the creator -->
                    <?php if ($auth->isAdmin() || $auth->getUserId() == $competition['created_by']): ?>
                        <a href="edit_competition.php?id=<?php echo $competition['competition_id']; ?>">Edit</a>
                        <a href="delete_competition.php?id=<?php echo $competition['competition_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>

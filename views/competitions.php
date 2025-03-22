<?php
require_once __DIR__ . '/../controllers/CompetitionController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$competitionController = new CompetitionController($db);

if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

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
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>

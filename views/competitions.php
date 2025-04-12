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
        <table class="competition-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competitions as $competition): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($competition['title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($competition['description'])); ?></td>
                        <td><?php echo $competition['start_date']; ?></td>
                        <td><?php echo $competition['end_date']; ?></td>
                        <td>
                            <a href="competition_details.php?id=<?php echo $competition['competition_id']; ?>">View</a>
                            <?php if ($auth->isAdmin() || $auth->getUserId() == $competition['created_by']): ?>
                                <a href="edit_competition.php?id=<?php echo $competition['competition_id']; ?>">Edit</a>
                                <a href="delete_competition.php?id=<?php echo $competition['competition_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

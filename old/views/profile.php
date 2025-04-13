<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../db.php';

session_start();

$auth = new AuthController($db);
$userController = new UserController($db);

// Check if the user is authenticated
if (!$auth->isAuthenticated()) {
    header("Location: login.php");
    exit();
}

$userId = $auth->getUserId();
$user = $userController->getUserById($userId);

// Handle form submission for updating the profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if ($userController->updateProfile($userId, $username, $email)) {
        $success = "Profile updated successfully.";
        $user = $userController->getUserById($userId);
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>User Profile</h2>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        
        <!-- Profile update form -->
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <button type="submit">Update Profile</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
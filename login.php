<?php
// login.php - User Login Page
require_once 'db.php'; // Ensure database connection is included
require_once 'controllers/AuthController.php';

$authController = new AuthController($db); // Pass the database connection
$error = ""; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if ($authController->login($email, $password, $remember)) {
        header("Location: dashboard.php"); // Redirect to dashboard after successful login
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (!empty($error)): ?>
            <p class='error'><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <label>
                <input type="checkbox" name="remember"> Remember Me
            </label>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>

<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db.php';

$auth = new AuthController($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $token = $auth->generatePasswordResetToken($email);

    if ($token) {
        // Use a relative path for the reset link
        $resetLink = "../reset_password.php?token=$token";
        $message = "Password reset link: <a href='$resetLink'>$resetLink</a>";
    } else {
        $message = "Failed to generate reset link. Please check your email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>
        <form method="post" action="">
            <label for="email">Enter your email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>

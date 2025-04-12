<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db.php';

$auth = new AuthController($db);
$message = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword === $confirmPassword) {
        $user = $auth->validateResetToken($token);
        if ($user && $auth->resetPassword($user['user_id'], $newPassword)) {
            $message = "Password reset successfully. <a href='login.php'>Login here</a>";
        } else {
            $message = "Invalid or expired token.";
        }
    } else {
        $message = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>
        <form method="post" action="">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>

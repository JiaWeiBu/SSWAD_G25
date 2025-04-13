<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../db.php';;

class AuthController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->autoLogin(); // Attempt auto-login if cookie is set
    }

    /**
     * Register a new user.
     * 
     * @param string $username The username of the new user.
     * @param string $email The email address of the new user.
     * @param string $password The password of the new user.
     * @return bool True if the user was successfully registered, false otherwise.
     * Example: $authController->register('JohnDoe', 'john@example.com', 'password123');
     */
    public function register($username, $email, $password) {
        return $this->userModel->register($username, $email, $password);
    }

    /**
     * Log in a user with optional "Remember Me" functionality.
     * 
     * @param string $email The email address of the user.
     * @param string $password The password of the user.
     * @param bool $remember Whether to remember the user for future sessions.
     * @return bool True if login was successful, false otherwise.
     * Example: $authController->login('john@example.com', 'password123', true);
     */
    public function login($email, $password, $remember = false) {
        $user = $this->userModel->login($email, $password);

        // Debugging: Check the result of the login method
        // var_dump($user);

        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie("auth_token", $token, time() + (86400 * 30), "/", "", false, true); // 30 days

                $stmt = $this->db->prepare("UPDATE Users SET remember_token = ? WHERE user_id = ?");
                $stmt->bind_param("si", $token, $user['user_id']);
                $stmt->execute();
            }

            return true;
        }
        return false;
    }

    /**
     * Automatically log in a user using a cookie token.
     * 
     * @return void
     * Example: $authController->autoLogin();
     */
    private function autoLogin() {
        if (isset($_COOKIE['auth_token'])) {
            $token = $_COOKIE['auth_token'];
            $stmt = $this->db->prepare("SELECT user_id, username FROM Users WHERE remember_token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
            }
        }
    }

    /**
     * Check if a user is authenticated.
     * 
     * @return bool True if the user is authenticated, false otherwise.
     * Example: $authController->isAuthenticated();
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the ID of the currently logged-in user.
     * 
     * @return int|null The user ID, or null if not authenticated.
     * Example: $userId = $authController->getUserId();
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get the username of the currently logged-in user.
     * 
     * @return string|null The username, or null if not authenticated.
     * Example: $username = $authController->getUsername();
     */
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }

    /**
     * Log out the current user.
     * 
     * @return void
     * Example: $authController->logout();
     */
    public function logout() {
        setcookie("auth_token", "", time() - 3600, "/"); // Expire the cookie
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }

    /**
     * Register a new user (static method).
     * 
     * @param string $username The username of the new user.
     * @param string $email The email address of the new user.
     * @param string $password The password of the new user.
     * @return bool True if the user was successfully registered, false otherwise.
     * Example: AuthController::registerUser('JohnDoe', 'john@example.com', 'password123');
     */
    public static function registerUser($username, $email, $password) {
        $db = new Database();
        
        // Check if username or email already exists
        $query = "SELECT user_id FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Username or email already exists
        }
        
        // Insert new user
        $query = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        return $stmt->execute();
    }

    /**
     * Log in a user with optional "Remember Me" functionality (static method).
     * 
     * @param string $email The email address of the user.
     * @param string $password The password of the user.
     * @param bool $remember Whether to remember the user for future sessions.
     * @return bool True if login was successful, false otherwise.
     * Example: AuthController::loginUser('john@example.com', 'password123', true);
     */
    public static function loginUser($email, $password, $remember = false) {
        $db = new Database();
        $stmt = $db->prepare("SELECT user_id, username, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie("auth_token", $token, time() + (86400 * 30), "/", "", false, true); // 30 days
                    
                    $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $token, $user['user_id']);
                    $stmt->execute();
                }
                
                return true;
            }
        }
        return false;
    }

    /**
     * Generate a password reset token for a user.
     * 
     * @param string $email The email address of the user.
     * @return string|false The reset token if successful, or false otherwise.
     * Example: $token = $authController->generatePasswordResetToken('john@example.com');
     */
    public function generatePasswordResetToken($email) {
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("UPDATE Users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    /**
     * Validate a password reset token.
     * 
     * @param string $token The reset token to validate.
     * @return array|null The user details if the token is valid, or null otherwise.
     * Example: $user = $authController->validateResetToken('token123');
     */
    public function validateResetToken($token) {
        $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Reset the password for a user.
     * 
     * @param int $userId The ID of the user.
     * @param string $newPassword The new password for the user.
     * @return bool True if the password was successfully reset, false otherwise.
     * Example: $authController->resetPassword(1, 'newPassword123');
     */
    public function resetPassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE Users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }

    /**
     * Check if the currently logged-in user is an admin.
     * 
     * @return bool True if the user is an admin, false otherwise.
     * Example: $authController->isAdmin();
     */
    public function isAdmin() {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $userId = $this->getUserId();
        $stmt = $this->db->prepare("SELECT role FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return isset($result['role']) && $result['role'] === 'admin';
    }
}
?>

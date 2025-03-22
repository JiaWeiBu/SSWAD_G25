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

    // Register new user
    public function register($username, $email, $password) {
        $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return "Email or username already exists!";
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $passwordHash);
        
        return $stmt->execute() ? true : "Registration failed!";
    }

    // User Login with Remember Me Option
    public function login($email, $password, $remember = false) {
        $stmt = $this->db->prepare("SELECT user_id, username, password_hash FROM Users WHERE email = ?");
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
                    
                    $stmt = $this->db->prepare("UPDATE Users SET remember_token = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $token, $user['user_id']);
                    $stmt->execute();
                }
                
                return true;
            }
        }
        return "Invalid email or password!";
    }

    // Auto Login using Cookie
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

    // Check if User is Logged In
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    // Get Current User ID
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Get Current Username
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }

    // Logout Function
    public function logout() {
        setcookie("auth_token", "", time() - 3600, "/"); // Expire the cookie
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }

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
}

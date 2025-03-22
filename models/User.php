<?php
require_once __DIR__ . '/../db.php';

class User {
    private $db;
    private $table = 'Users';

    public function __construct($db) {
        $this->db = $db;
    }

    // Register a new user
    public function register($username, $email, $password, $role = 'user') {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $passwordHash, $role);
        return $stmt->execute();
    }

    // Login user and return user data if successful
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT user_id, username, password_hash FROM {$this->table} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                return $user; // Return user details for session creation
            }
        }
        return false; // Login failed
    }

    // Get user details by ID
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Find user by email
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT user_id, username, email FROM {$this->table} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update user details
    public function updateUser($userId, $username, $email) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET username = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);
        return $stmt->execute();
    }

    // Update user password
    public function updatePassword($userId, $oldPassword, $newPassword) {
        // Fetch the current password
        $stmt = $this->db->prepare("SELECT password FROM {$this->table} WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the old password
        if (!password_verify($oldPassword, $user['password'])) {
            return false;
        }

        // Hash the new password and update it
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
        return $stmt->execute([$newPasswordHash, $userId]);
    }
}
?>

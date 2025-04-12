<?php
require_once __DIR__ . '/../db.php';

class User {
    private $db;
    private $table = 'Users';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Register a new user.
     * 
     * @param string $username The username of the new user.
     * @param string $email The email address of the new user.
     * @param string $password The password of the new user.
     * @param string $role The role of the user (default is 'user').
     * @return bool True if the user was successfully registered, false otherwise.
     * Example: $user->register('JohnDoe', 'john@example.com', 'password123');
     */
    public function register($username, $email, $password, $role = 'user') {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $passwordHash, $role);
        return $stmt->execute();
    }

    /**
     * Login a user and return user data if successful.
     * 
     * @param string $email The email address of the user.
     * @param string $password The password of the user.
     * @return array|false User data if login is successful, false otherwise.
     * Example: $user->login('john@example.com', 'password123');
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT user_id, username, password_hash FROM {$this->table} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Debugging: Check if the query returned any rows
        // echo "Rows found: " . $result->num_rows;

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Debugging: Check the fetched user data
            // var_dump($user);

            if (password_verify($password, $user['password_hash'])) {
                // Debugging: Password verification success
                // echo "Password verified!";
                return $user; // Return user details for session creation
            } else {
                // Debugging: Password verification failed
                // echo "Password verification failed!";
            }
        }
        return false; // Login failed
    }

    /**
     * Get user details by ID.
     * 
     * @param int $userId The ID of the user.
     * @return array|null User details as an associative array, or null if not found.
     * Example: $user->getUserById(1);
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Find a user by email.
     * 
     * @param string $email The email address of the user.
     * @return array|null User details as an associative array, or null if not found.
     * Example: $user->getUserByEmail('john@example.com');
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT user_id, username, email FROM {$this->table} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update user details.
     * 
     * @param int $userId The ID of the user.
     * @param string $username The new username.
     * @param string $email The new email address.
     * @return bool True if the update was successful, false otherwise.
     * Example: $user->updateUser(1, 'JohnDoe', 'john@example.com');
     */
    public function updateUser($userId, $username, $email) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET username = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);
        return $stmt->execute();
    }

    /**
     * Update the user's password.
     * 
     * @param int $userId The ID of the user.
     * @param string $oldPassword The current password of the user.
     * @param string $newPassword The new password for the user.
     * @return bool True if the password was successfully updated, false otherwise.
     * Example: $user->updatePassword(1, 'oldPassword123', 'newPassword123');
     */
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

<?php
// controllers/UserController.php
require_once __DIR__ . '/../models/User.php';
require_once 'AuthController.php';
require_once __DIR__ . '/../db.php'; // Corrected path to db.php

class UserController {
    private $db;
    private $auth;
    private $userModel;
    private $tableName = 'users'; // Add table name variable

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new AuthController($db);
        $this->userModel = new User($db); // Instantiate User model
    }

    /**
     * Get user details by ID.
     * 
     * @param int $userId The ID of the user.
     * @return array|null The user details as an associative array, or null if not found.
     * Example: $userController->getUserById(1);
     */
    public function getUserById($userId) {
        return $this->userModel->getUserById($userId);
    }

    /**
     * Get the currently logged-in user's details.
     * 
     * @return array|null The user details as an associative array, or null if not authenticated.
     * Example: $userController->getCurrentUser();
     */
    public function getCurrentUser() {
        if (!$this->auth->isAuthenticated()) {
            return null;
        }
        $userId = $this->auth->getUserId();
        return $this->getUserById($userId);
    }

    /**
     * Update the user's profile.
     * 
     * @param int $userId The ID of the user.
     * @param string $username The updated username.
     * @param string $email The updated email address.
     * @return bool True if the profile was successfully updated, false otherwise.
     * Example: $userController->updateProfile(1, 'JohnDoe', 'john@example.com');
     */
    public function updateProfile($userId, $username, $email) {
        return $this->userModel->updateUser($userId, $username, $email);
    }

    /**
     * Change the user's password.
     * 
     * @param int $userId The ID of the user.
     * @param string $oldPassword The current password of the user.
     * @param string $newPassword The new password for the user.
     * @return bool True if the password was successfully changed, false otherwise.
     * Example: $userController->changePassword(1, 'oldPassword123', 'newPassword123');
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        return $this->userModel->updatePassword($userId, $oldPassword, $newPassword);
    }
}
?>

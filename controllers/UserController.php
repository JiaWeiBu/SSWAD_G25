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

    // Get user details by ID
    public function getUserById($userId) {
        return $this->userModel->getUserById($userId);
    }

    // Get currently logged-in user
    public function getCurrentUser() {
        if (!$this->auth->isAuthenticated()) {
            return null;
        }
        $userId = $this->auth->getUserId();
        return $this->getUserById($userId);
    }

    // Update user profile
    public function updateProfile($userId, $username, $email) {
        return $this->userModel->updateUser($userId, $username, $email);
    }

    // Change user password
    public function changePassword($userId, $oldPassword, $newPassword) {
        return $this->userModel->updatePassword($userId, $oldPassword, $newPassword);
    }
}
?>

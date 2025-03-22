<?php
require_once __DIR__ . '/../models/Recipe.php';
require_once 'AuthController.php';

class RecipeController {
    private $db;
    private $auth;
    private $recipeModel;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new AuthController($db);
        $this->recipeModel = new Recipe($db);
    }

    // Create a new recipe
    public function createRecipe($title, $description, $ingredients, $instructions, $imagePath) {
        if (!$this->auth->isAuthenticated()) {
            return "Unauthorized access.";
        }
        $userId = $this->auth->getUserId();
        return $this->recipeModel->create($userId, $title, $description, $ingredients, $instructions, $imagePath);
    }

    // Fetch all recipes
    public function getAllRecipes() {
        return $this->recipeModel->getAll();
    }

    // Fetch a recipe by ID
    public function getRecipeById($recipeId) {
        return $this->recipeModel->getById($recipeId);
    }

    // Fetch recipes by user ID
    public function getRecipesByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM recipes WHERE user_id = ?");
        $stmt->execute([$userId]);
        $recipes = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $recipes[] = $row;
        }
        return $recipes;
    }

    // Update a recipe
    public function updateRecipe($recipeId, $title, $description, $ingredients, $instructions, $imagePath) {
        if (!$this->auth->isAuthenticated()) {
            return "Unauthorized access.";
        }
        $userId = $this->auth->getUserId();
        return $this->recipeModel->update($recipeId, $userId, $title, $description, $ingredients, $instructions, $imagePath);
    }

    // Delete a recipe
    public function deleteRecipe($recipeId) {
        if (!$this->auth->isAuthenticated()) {
            return "Unauthorized access.";
        }
        $userId = $this->auth->getUserId();
        return $this->recipeModel->delete($recipeId, $userId);
    }
}
?>

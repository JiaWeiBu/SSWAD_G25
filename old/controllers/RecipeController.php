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

    /**
     * Create a new recipe.
     * 
     * @param string $title The title of the recipe.
     * @param string $description A brief description of the recipe.
     * @param string $ingredients The ingredients required for the recipe.
     * @param string $instructions The preparation instructions for the recipe.
     * @param string $imagePath The file path of the recipe image.
     * @return bool|string True if the recipe was successfully created, or an error message otherwise.
     * Example: $recipeController->createRecipe('Pasta', 'Delicious pasta', 'Pasta, Sauce', 'Boil pasta...', '/images/pasta.jpg');
     */
    public function createRecipe($title, $description, $ingredients, $instructions, $imagePath) {
        if (!$this->auth->isAuthenticated()) {
            return "Unauthorized access.";
        }
        $userId = $this->auth->getUserId();
        return $this->recipeModel->create($userId, $title, $description, $ingredients, $instructions, $imagePath);
    }

    /**
     * Fetch all recipes.
     * 
     * @return array An array of all recipes.
     * Example: $recipes = $recipeController->getAllRecipes();
     */
    public function getAllRecipes() {
        return $this->recipeModel->getAll();
    }

    /**
     * Fetch a recipe by its ID.
     * 
     * @param int $recipeId The ID of the recipe.
     * @return array|null The recipe details as an associative array, or null if not found.
     * Example: $recipeController->getRecipeById(1);
     */
    public function getRecipeById($recipeId) {
        return $this->recipeModel->getById($recipeId);
    }

    /**
     * Fetch recipes created by a specific user.
     * 
     * @param int $userId The ID of the user.
     * @return array An array of recipes created by the user.
     * Example: $recipes = $recipeController->getRecipesByUserId(1);
     */
    public function getRecipesByUserId($userId) {
        return $this->recipeModel->getRecipesByUser($userId); // Delegate to the model
    }

    /**
     * Update a recipe.
     * 
     * @param int $recipeId The ID of the recipe to update.
     * @param string $title The updated title of the recipe.
     * @param string $description The updated description.
     * @param string $ingredients The updated ingredients.
     * @param string $instructions The updated instructions.
     * @param string $imagePath The updated file path of the recipe image.
     * @return bool|string True if the recipe was successfully updated, or an error message otherwise.
     * Example: $recipeController->updateRecipe(1, 'Updated Pasta', 'Updated description', 'Updated ingredients', 'Updated instructions', '/images/updated_pasta.jpg');
     */
    public function updateRecipe($recipeId, $title, $description, $ingredients, $instructions, $imagePath) {
        if (!$this->auth->isAuthenticated()) {
            return "Unauthorized access.";
        }
        $userId = $this->auth->getUserId();
        return $this->recipeModel->update($recipeId, $userId, $title, $description, $ingredients, $instructions, $imagePath);
    }

    /**
     * Delete a recipe.
     * 
     * @param int $recipeId The ID of the recipe to delete.
     * @return bool|string True if the recipe was successfully deleted, or an error message otherwise.
     * Example: $recipeController->deleteRecipe(1);
     */
    public function deleteRecipe($recipeId) {
        if (!$this->auth->isAuthenticated()) {
            return "Unauthorized access.";
        }
        $userId = $this->auth->getUserId();
        return $this->recipeModel->delete($recipeId, $userId);
    }
}
?>

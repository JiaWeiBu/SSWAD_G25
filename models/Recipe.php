<?php
require_once __DIR__ . '/../db.php';

class Recipe {
    private $db;
    private $table = 'Recipes';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new recipe.
     * 
     * @param int $userId The ID of the user creating the recipe.
     * @param string $title The title of the recipe.
     * @param string $cuisine The cuisine type of the recipe.
     * @param string $description A brief description of the recipe.
     * @param string $ingredients The ingredients required for the recipe.
     * @param string $instructions The preparation instructions for the recipe.
     * @return bool True if the recipe was successfully created, false otherwise.
     * Example: $recipe->createRecipe(1, 'Pasta', 'Italian', 'Delicious pasta', 'Pasta, Sauce', 'Boil pasta...');
     */
    public function createRecipe($userId, $title, $cuisine, $description, $ingredients, $instructions) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, title, cuisine, description, ingredients, instructions) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $userId, $title, $cuisine, $description, $ingredients, $instructions);
        return $stmt->execute();
    }

    /**
     * Get all recipes.
     * 
     * @return array An array of all recipes.
     * Example: $recipes = $recipe->getAllRecipes();
     */
    public function getAllRecipes() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a recipe by its ID.
     * 
     * @param int $recipeId The ID of the recipe.
     * @return array|null The recipe details as an associative array, or null if not found.
     * Example: $recipe->getRecipeById(1);
     */
    public function getRecipeById($recipeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get recipes created by a specific user.
     * 
     * @param int $userId The ID of the user.
     * @return array An array of recipes created by the user.
     * Example: $recipes = $recipe->getRecipesByUser(1);
     */
    public function getRecipesByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update a recipe.
     * 
     * @param int $recipeId The ID of the recipe to update.
     * @param string $title The updated title of the recipe.
     * @param string $cuisine The updated cuisine type.
     * @param string $description The updated description.
     * @param string $ingredients The updated ingredients.
     * @param string $instructions The updated instructions.
     * @return bool True if the recipe was successfully updated, false otherwise.
     * Example: $recipe->updateRecipe(1, 'Updated Pasta', 'Italian', 'Updated description', 'Updated ingredients', 'Updated instructions');
     */
    public function updateRecipe($recipeId, $title, $cuisine, $description, $ingredients, $instructions) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET title = ?, cuisine = ?, description = ?, ingredients = ?, instructions = ? WHERE recipe_id = ?");
        $stmt->bind_param("sssssi", $title, $cuisine, $description, $ingredients, $instructions, $recipeId);
        return $stmt->execute();
    }

    /**
     * Delete a recipe.
     * 
     * @param int $recipeId The ID of the recipe to delete.
     * @return bool True if the recipe was successfully deleted, false otherwise.
     * Example: $recipe->deleteRecipe(1);
     */
    public function deleteRecipe($recipeId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipeId);
        return $stmt->execute();
    }
}
?>

<?php
require_once __DIR__ . '/../db.php';

class Recipe {
    private $db;
    private $table = 'Recipes';

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new recipe
    public function createRecipe($userId, $title, $cuisine, $description, $ingredients, $instructions) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, title, cuisine, description, ingredients, instructions) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $userId, $title, $cuisine, $description, $ingredients, $instructions);
        return $stmt->execute();
    }

    // Get all recipes
    public function getAllRecipes() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get a recipe by ID
    public function getRecipeById($recipeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get recipes by a specific user
    public function getRecipesByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Update a recipe
    public function updateRecipe($recipeId, $title, $cuisine, $description, $ingredients, $instructions) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET title = ?, cuisine = ?, description = ?, ingredients = ?, instructions = ? WHERE recipe_id = ?");
        $stmt->bind_param("sssssi", $title, $cuisine, $description, $ingredients, $instructions, $recipeId);
        return $stmt->execute();
    }

    // Delete a recipe
    public function deleteRecipe($recipeId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipeId);
        return $stmt->execute();
    }
}
?>

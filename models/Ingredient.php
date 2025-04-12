<?php
require_once __DIR__ . '/../db.php';

class Ingredient {
    private $db;
    private $table = 'Ingredients';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a new ingredient.
     * 
     * @param string $name The name of the ingredient.
     * @param string $description A description of the ingredient.
     * @return bool True if the ingredient was successfully added, false otherwise.
     * Example: $ingredient->addIngredient('Tomato', 'Fresh red tomatoes');
     */
    public function addIngredient($name, $description) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        return $stmt->execute();
    }

    /**
     * Get all ingredients.
     * 
     * @return array An array of all ingredients.
     * Example: $ingredients = $ingredient->getAllIngredients();
     */
    public function getAllIngredients() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name ASC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get an ingredient by its ID.
     * 
     * @param int $ingredientId The ID of the ingredient.
     * @return array|null The ingredient details as an associative array, or null if not found.
     * Example: $ingredient->getIngredientById(1);
     */
    public function getIngredientById($ingredientId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE ingredient_id = ?");
        $stmt->bind_param("i", $ingredientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update an ingredient.
     * 
     * @param int $ingredientId The ID of the ingredient to update.
     * @param string $name The updated name of the ingredient.
     * @param string $description The updated description of the ingredient.
     * @return bool True if the ingredient was successfully updated, false otherwise.
     * Example: $ingredient->updateIngredient(1, 'Updated Tomato', 'Updated description');
     */
    public function updateIngredient($ingredientId, $name, $description) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = ?, description = ? WHERE ingredient_id = ?");
        $stmt->bind_param("ssi", $name, $description, $ingredientId);
        return $stmt->execute();
    }

    /**
     * Delete an ingredient.
     * 
     * @param int $ingredientId The ID of the ingredient to delete.
     * @return bool True if the ingredient was successfully deleted, false otherwise.
     * Example: $ingredient->deleteIngredient(1);
     */
    public function deleteIngredient($ingredientId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE ingredient_id = ?");
        $stmt->bind_param("i", $ingredientId);
        return $stmt->execute();
    }
}
?>

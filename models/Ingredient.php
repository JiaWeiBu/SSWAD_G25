<?php
require_once __DIR__ . '/../db.php';

class Ingredient {
    private $db;
    private $table = 'Ingredients';

    public function __construct($db) {
        $this->db = $db;
    }

    // Add a new ingredient
    public function addIngredient($name, $description) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        return $stmt->execute();
    }

    // Get all ingredients
    public function getAllIngredients() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name ASC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get an ingredient by ID
    public function getIngredientById($ingredientId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE ingredient_id = ?");
        $stmt->bind_param("i", $ingredientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update an ingredient
    public function updateIngredient($ingredientId, $name, $description) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = ?, description = ? WHERE ingredient_id = ?");
        $stmt->bind_param("ssi", $name, $description, $ingredientId);
        return $stmt->execute();
    }

    // Delete an ingredient
    public function deleteIngredient($ingredientId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE ingredient_id = ?");
        $stmt->bind_param("i", $ingredientId);
        return $stmt->execute();
    }
}
?>

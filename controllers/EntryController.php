<?php
require_once '../models/CompetitionEntry.php';
require_once '../models/Competition.php';
require_once '../models/Recipe.php';
require_once 'AuthController.php';
require_once '../db.php';
require_once '../models/Entry.php';

class EntryController {
    private $db;
    private $auth;
    
    public function __construct($db) {
        $this->db = $db;
        $this->auth = new AuthController($db);
    }

    // Submit a recipe to a competition
    public function submitEntry($competitionId, $recipeId) {
        if (!$this->auth->isAuthenticated()) {
            return "User not authenticated.";
        }
        
        $userId = $this->auth->getUserId();
        $competitionEntry = new CompetitionEntry($this->db);
        
        // Check if competition exists
        $competition = new Competition($this->db);
        if (!$competition->getCompetitionById($competitionId)) {
            return "Competition not found.";
        }
        
        // Check if recipe exists and belongs to the user
        $recipe = new Recipe($this->db);
        $userRecipe = $recipe->getRecipeById($recipeId);
        if (!$userRecipe || $userRecipe['user_id'] !== $userId) {
            return "Invalid recipe submission.";
        }
        
        // Submit the entry
        return $competitionEntry->submitEntry($competitionId, $recipeId, $userId);
    }
    
    // Get all entries for a competition
    public function getEntriesByCompetition($competitionId) {
        $competitionEntry = new CompetitionEntry($this->db);
        return $competitionEntry->getEntriesByCompetition($competitionId);
    }
    
    // Get all entries submitted by a user
    public function getEntriesByUser($userId) {
        $competitionEntry = new CompetitionEntry($this->db);
        return $competitionEntry->getEntriesByUser($userId);
    }

    // Get entries by competition ID
    public function getEntriesByCompetitionId($competitionId) {
        $competitionEntry = new CompetitionEntry($this->db);
        return $competitionEntry->getEntriesByCompetition($competitionId);
    }
}
?>
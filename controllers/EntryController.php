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

    /**
     * Submit a recipe to a competition.
     * 
     * @param int $competitionId The ID of the competition.
     * @param int $recipeId The ID of the recipe being submitted.
     * @return bool|string True if the entry was successfully submitted, or an error message otherwise.
     * Example: $entryController->submitEntry(1, 10);
     */
    public function submitEntry($competitionId, $recipeId) {
        if (!$this->auth->isAuthenticated()) {
            return "User not authenticated.";
        }

        $userId = $this->auth->getUserId();
        $competitionEntry = new CompetitionEntry($this->db);

        // Delegate the logic to the model
        return $competitionEntry->submitEntry($competitionId, $recipeId, $userId);
    }
    
    /**
     * Get all entries for a specific competition.
     * 
     * @param int $competitionId The ID of the competition.
     * @return array An array of entries for the specified competition.
     * Example: $entries = $entryController->getEntriesByCompetition(1);
     */
    public function getEntriesByCompetition($competitionId) {
        $competitionEntry = new CompetitionEntry($this->db);
        return $competitionEntry->getEntriesByCompetition($competitionId);
    }
    
    /**
     * Get all entries submitted by a specific user.
     * 
     * @param int $userId The ID of the user.
     * @return array An array of entries submitted by the user.
     * Example: $entries = $entryController->getEntriesByUser(1);
     */
    public function getEntriesByUser($userId) {
        $competitionEntry = new CompetitionEntry($this->db);
        return $competitionEntry->getEntriesByUser($userId);
    }

    /**
     * Get entries by competition ID.
     * 
     * @param int $competitionId The ID of the competition.
     * @return array An array of entries for the specified competition.
     * Example: $entries = $entryController->getEntriesByCompetitionId(1);
     */
    public function getEntriesByCompetitionId($competitionId) {
        $competitionEntry = new CompetitionEntry($this->db);
        return $competitionEntry->getEntriesByCompetition($competitionId);
    }

    /**
     * Prepare the submit entry page by fetching recipes for the user.
     * 
     * @param int $userId The ID of the user.
     * @return void
     * Example: $entryController->prepareSubmitEntryPage(1);
     */
    public function prepareSubmitEntryPage($userId) {
        $recipeController = new RecipeController($this->db);
        $recipes = $recipeController->getRecipesByUserId($userId);
        $_SESSION['recipes'] = $recipes;
    }
}
?>
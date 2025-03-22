<?php
require_once '../models/Competition.php';
require_once 'AuthController.php';
require_once __DIR__ . '/../db.php';

class CompetitionController {
    private $db;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new AuthController($db);
    }

    // Create a new competition
    public function createCompetition($title, $description, $startDate, $endDate) {
        if (!$this->auth->isAuthenticated()) {
            return ["success" => false, "message" => "Unauthorized access."];
        }
        
        $competition = new Competition($this->db);
        $result = $competition->createCompetition($title, $description, $startDate, $endDate);
        
        return $result ? ["success" => true, "message" => "Competition created successfully."] :
                         ["success" => false, "message" => "Failed to create competition."];
    }

    // Get all competitions
    public function getAllCompetitions() {
        $competition = new Competition($this->db);
        return $competition->getAll();
    }

    // Get a specific competition by ID
    public function getCompetitionById($competitionId) {
        $competition = new Competition($this->db);
        return $competition->getById($competitionId);
    }

    // Update competition details
    public function updateCompetition($competitionId, $title, $description, $startDate, $endDate) {
        if (!$this->auth->isAuthenticated()) {
            return ["success" => false, "message" => "Unauthorized access."];
        }
        
        $competition = new Competition($this->db);
        $result = $competition->updateCompetition($competitionId, $title, $description, $startDate, $endDate);
        
        return $result ? ["success" => true, "message" => "Competition updated successfully."] :
                         ["success" => false, "message" => "Failed to update competition."];
    }

    // Delete a competition
    public function deleteCompetition($competitionId) {
        if (!$this->auth->isAuthenticated()) {
            return ["success" => false, "message" => "Unauthorized access."];
        }
        
        $competition = new Competition($this->db);
        $result = $competition->delete($competitionId);
        
        return $result ? ["success" => true, "message" => "Competition deleted successfully."] :
                         ["success" => false, "message" => "Failed to delete competition."];
    }
}
?>

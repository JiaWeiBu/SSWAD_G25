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

    /**
     * Create a new competition.
     * 
     * @param string $title The title of the competition.
     * @param string $description A description of the competition.
     * @param string $startDate The start date of the competition.
     * @param string $endDate The end date of the competition.
     * @return array An array containing the success status and message.
     * Example: $competitionController->createCompetition('Cooking Contest', 'Best dish wins!', '2023-01-01', '2023-01-31');
     */
    public function createCompetition($title, $description, $startDate, $endDate) {
        if (!$this->auth->isAuthenticated()) {
            return ["success" => false, "message" => "Unauthorized access."];
        }

        $userId = $this->auth->getUserId();
        $competition = new Competition($this->db);
        $result = $competition->createCompetition($title, $description, $startDate, $endDate, $userId);

        return $result ? ["success" => true, "message" => "Competition created successfully."] :
                         ["success" => false, "message" => "Failed to create competition."];
    }

    /**
     * Get all competitions.
     * 
     * @return array An array of all competitions.
     * Example: $competitions = $competitionController->getAllCompetitions();
     */
    public function getAllCompetitions() {
        $competition = new Competition($this->db);
        return $competition->getAll(); // Delegate to the model
    }

    /**
     * Get a specific competition by its ID.
     * 
     * @param int $competitionId The ID of the competition.
     * @return array|null The competition details as an associative array, or null if not found.
     * Example: $competitionController->getCompetitionById(1);
     */
    public function getCompetitionById($competitionId) {
        $competition = new Competition($this->db);
        return $competition->getById($competitionId); // Delegate to the model
    }

    /**
     * Update competition details.
     * 
     * @param int $competitionId The ID of the competition to update.
     * @param string $title The updated title of the competition.
     * @param string $description The updated description.
     * @param string $startDate The updated start date.
     * @param string $endDate The updated end date.
     * @return array An array containing the success status and message.
     * Example: $competitionController->updateCompetition(1, 'Updated Contest', 'Updated description', '2023-02-01', '2023-02-28');
     */
    public function updateCompetition($competitionId, $title, $description, $startDate, $endDate) {
        if (!$this->auth->isAuthenticated()) {
            return ["success" => false, "message" => "Unauthorized access."];
        }

        $userId = $this->auth->getUserId();
        $competition = new Competition($this->db);

        if (!$this->auth->isAdmin() && !$competition->isCreator($competitionId, $userId)) {
            return ["success" => false, "message" => "Unauthorized access."];
        }

        $result = $competition->updateCompetition($competitionId, $title, $description, $startDate, $endDate);

        return $result ? ["success" => true, "message" => "Competition updated successfully."] :
                         ["success" => false, "message" => "Failed to update competition."];
    }

    /**
     * Delete a competition.
     * 
     * @param int $competitionId The ID of the competition to delete.
     * @return array An array containing the success status and message.
     * Example: $competitionController->deleteCompetition(1);
     */
    public function deleteCompetition($competitionId) {
        if (!$this->auth->isAuthenticated() || !$this->auth->isAdmin()) {
            return ["success" => false, "message" => "Unauthorized access."];
        }

        $competition = new Competition($this->db);
        $result = $competition->deleteCompetition($competitionId);

        return $result ? ["success" => true, "message" => "Competition deleted successfully."] :
                         ["success" => false, "message" => "Failed to delete competition."];
    }
}
?>

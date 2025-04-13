<?php
require_once __DIR__ . '/../db.php';

class Competition {
    private $db;
    private $table = 'Competitions';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new competition.
     * 
     * @param string $title The title of the competition.
     * @param string $description A description of the competition.
     * @param string $start_date The start date of the competition.
     * @param string $end_date The end date of the competition.
     * @param int $created_by The ID of the user creating the competition.
     * @return bool True if the competition was successfully created, false otherwise.
     * Example: $competition->createCompetition('Cooking Contest', 'Best dish wins!', '2023-01-01', '2023-01-31', 1);
     */
    public function createCompetition($title, $description, $start_date, $end_date, $created_by) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (title, description, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $created_by);
        return $stmt->execute();
    }

    /**
     * Update competition details.
     * 
     * @param int $competitionId The ID of the competition to update.
     * @param string $title The updated title of the competition.
     * @param string $description The updated description.
     * @param string $start_date The updated start date.
     * @param string $end_date The updated end date.
     * @return bool True if the competition was successfully updated, false otherwise.
     * Example: $competition->updateCompetition(1, 'Updated Contest', 'Updated description', '2023-02-01', '2023-02-28');
     */
    public function updateCompetition($competitionId, $title, $description, $start_date, $end_date) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET title = ?, description = ?, start_date = ?, end_date = ? WHERE competition_id = ?");
        $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $competitionId);
        return $stmt->execute();
    }

    /**
     * Delete a competition.
     * 
     * @param int $competitionId The ID of the competition to delete.
     * @return bool True if the competition was successfully deleted, false otherwise.
     * Example: $competition->deleteCompetition(1);
     */
    public function deleteCompetition($competitionId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        return $stmt->execute();
    }

    /**
     * Check if a user is the creator of a competition.
     * 
     * @param int $competitionId The ID of the competition.
     * @param int $userId The ID of the user.
     * @return bool True if the user is the creator, false otherwise.
     * Example: $competition->isCreator(1, 1);
     */
    public function isCreator($competitionId, $userId) {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE competition_id = ? AND created_by = ?");
        $stmt->bind_param("ii", $competitionId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Get all competitions.
     * 
     * @return array An array of all competitions.
     * Example: $competitions = $competition->getAll();
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY start_date DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get competition details by ID.
     * 
     * @param int $competitionId The ID of the competition.
     * @return array|null The competition details as an associative array, or null if not found.
     * Example: $competition->getById(1);
     */
    public function getById($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>

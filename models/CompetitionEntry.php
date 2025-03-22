<?php
require_once __DIR__ . '/../db.php';

class CompetitionEntry {
    private $db;
    private $table = 'CompetitionEntries';

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new competition entry
    public function addEntry($competitionId, $userId, $recipeId) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (competition_id, user_id, recipe_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $competitionId, $userId, $recipeId);
        return $stmt->execute();
    }

    // Submit a new entry
    public function submitEntry($competitionId, $recipeId, $userId) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (competition_id, user_id, recipe_id, submission) VALUES (?, ?, ?, ?)");
        $submission = ''; // Assuming submission is an empty string initially
        $stmt->bind_param("iiis", $competitionId, $userId, $recipeId, $submission);
        return $stmt->execute();
    }

    // Get all entries for a competition
    public function getEntriesByCompetition($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get entries by user ID
    public function getEntriesByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Check if a user already joined a competition
    public function hasUserJoined($competitionId, $userId) {
        $stmt = $this->db->prepare("SELECT entry_id FROM {$this->table} WHERE competition_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $competitionId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Delete a competition entry
    public function deleteEntry($entryId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        return $stmt->execute();
    }
}
?>

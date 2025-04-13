<?php
require_once __DIR__ . '/../db.php';

class CompetitionEntry {
    private $db;
    private $table = 'CompetitionEntries';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a new competition entry.
     * 
     * @param int $competitionId The ID of the competition.
     * @param int $userId The ID of the user submitting the entry.
     * @param int $recipeId The ID of the recipe being submitted.
     * @return bool True if the entry was successfully added, false otherwise.
     * Example: $entry->addEntry(1, 1, 10);
     */
    public function addEntry($competitionId, $userId, $recipeId) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (competition_id, user_id, recipe_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $competitionId, $userId, $recipeId);
        return $stmt->execute();
    }

    /**
     * Submit a new entry with additional submission content.
     * 
     * @param int $competitionId The ID of the competition.
     * @param int $recipeId The ID of the recipe being submitted.
     * @param int $userId The ID of the user submitting the entry.
     * @return bool True if the entry was successfully submitted, false otherwise.
     * Example: $entry->submitEntry(1, 10, 1);
     */
    public function submitEntry($competitionId, $recipeId, $userId) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (competition_id, user_id, recipe_id, submission) VALUES (?, ?, ?, ?)");
        $submission = ''; // Assuming submission is an empty string initially
        $stmt->bind_param("iiis", $competitionId, $userId, $recipeId, $submission);
        return $stmt->execute();
    }

    /**
     * Get all entries for a specific competition.
     * 
     * @param int $competitionId The ID of the competition.
     * @return array An array of entries for the specified competition.
     * Example: $entries = $entry->getEntriesByCompetition(1);
     */
    public function getEntriesByCompetition($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all entries submitted by a specific user.
     * 
     * @param int $userId The ID of the user.
     * @return array An array of entries submitted by the user.
     * Example: $entries = $entry->getEntriesByUser(1);
     */
    public function getEntriesByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if a user has already joined a competition.
     * 
     * @param int $competitionId The ID of the competition.
     * @param int $userId The ID of the user.
     * @return bool True if the user has already joined, false otherwise.
     * Example: $entry->hasUserJoined(1, 1);
     */
    public function hasUserJoined($competitionId, $userId) {
        $stmt = $this->db->prepare("SELECT entry_id FROM {$this->table} WHERE competition_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $competitionId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Delete a competition entry.
     * 
     * @param int $entryId The ID of the entry to delete.
     * @return bool True if the entry was successfully deleted, false otherwise.
     * Example: $entry->deleteEntry(1);
     */
    public function deleteEntry($entryId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        return $stmt->execute();
    }
}
?>

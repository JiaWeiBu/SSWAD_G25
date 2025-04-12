<?php
require_once __DIR__ . '/../db.php';

class Entry {
    private $db;
    private $table = 'Entries';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new entry.
     * 
     * @param int $competitionId The ID of the competition.
     * @param int $userId The ID of the user submitting the entry.
     * @param string $submission The content of the submission.
     * @return bool True if the entry was successfully created, false otherwise.
     * Example: $entry->createEntry(1, 1, 'My recipe submission');
     */
    public function createEntry($competitionId, $userId, $submission) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (competition_id, user_id, submission) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $competitionId, $userId, $submission);
        return $stmt->execute();
    }

    /**
     * Get all entries.
     * 
     * @return array An array of all entries.
     * Example: $entries = $entry->getAllEntries();
     */
    public function getAllEntries() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a specific entry by its ID.
     * 
     * @param int $entryId The ID of the entry.
     * @return array|null The entry details as an associative array, or null if not found.
     * Example: $entry->getEntryById(1);
     */
    public function getEntryById($entryId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update an entry's details.
     * 
     * @param int $entryId The ID of the entry to update.
     * @param string $submission The updated submission content.
     * @return bool True if the entry was successfully updated, false otherwise.
     * Example: $entry->updateEntry(1, 'Updated submission content');
     */
    public function updateEntry($entryId, $submission) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET submission = ? WHERE entry_id = ?");
        $stmt->bind_param("si", $submission, $entryId);
        return $stmt->execute();
    }

    /**
     * Delete an entry.
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

    /**
     * Get entries by competition ID.
     * 
     * @param int $competitionId The ID of the competition.
     * @return array An array of entries for the specified competition.
     * Example: $entries = $entry->getByCompetitionId(1);
     */
    public function getByCompetitionId($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
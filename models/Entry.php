<?php
require_once __DIR__ . '/../db.php';

class Entry {
    private $db;
    private $table = 'Entries';

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new entry
    public function createEntry($competitionId, $userId, $submission) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (competition_id, user_id, submission) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $competitionId, $userId, $submission);
        return $stmt->execute();
    }

    // Get all entries
    public function getAllEntries() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get a specific entry by ID
    public function getEntryById($entryId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update entry details
    public function updateEntry($entryId, $submission) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET submission = ? WHERE entry_id = ?");
        $stmt->bind_param("si", $submission, $entryId);
        return $stmt->execute();
    }

    // Delete an entry
    public function deleteEntry($entryId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        return $stmt->execute();
    }

    // Get entries by competition ID
    public function getByCompetitionId($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
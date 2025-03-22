<?php
require_once __DIR__ . '/../db.php';

class Vote {
    private $db;
    private $table = 'Votes';

    public function __construct($db) {
        $this->db = $db;
    }

    // Add a vote to a competition entry
    public function addVote($userId, $entryId) {
        if ($this->hasUserVoted($userId, $entryId)) {
            return false; // Prevent duplicate voting
        }

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, entry_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $entryId);
        return $stmt->execute();
    }

    // Check if a user has already voted for an entry
    public function hasUserVoted($userId, $entryId) {
        $stmt = $this->db->prepare("SELECT vote_id FROM {$this->table} WHERE user_id = ? AND entry_id = ?");
        $stmt->bind_param("ii", $userId, $entryId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Count total votes for an entry
    public function countVotes($entryId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS vote_count FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['vote_count'] ?? 0;
    }

    // Remove a vote (optional feature)
    public function removeVote($userId, $entryId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND entry_id = ?");
        $stmt->bind_param("ii", $userId, $entryId);
        return $stmt->execute();
    }
}
?>

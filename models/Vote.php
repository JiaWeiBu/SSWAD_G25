<?php
require_once __DIR__ . '/../db.php';

class Vote {
    private $db;
    private $table = 'Votes';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a vote to a competition entry.
     * 
     * @param int $userId The ID of the user casting the vote.
     * @param int $entryId The ID of the competition entry being voted for.
     * @return bool True if the vote was successfully added, false otherwise.
     * Example: $vote->addVote(1, 10);
     */
    public function addVote($userId, $entryId) {
        if ($this->hasUserVoted($userId, $entryId)) {
            return false; // Prevent duplicate voting
        }

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, entry_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $entryId);
        return $stmt->execute();
    }

    /**
     * Check if a user has already voted for a specific entry.
     * 
     * @param int $userId The ID of the user.
     * @param int $entryId The ID of the competition entry.
     * @return bool True if the user has already voted, false otherwise.
     * Example: $vote->hasUserVoted(1, 10);
     */
    public function hasUserVoted($userId, $entryId) {
        $stmt = $this->db->prepare("SELECT vote_id FROM {$this->table} WHERE user_id = ? AND entry_id = ?");
        $stmt->bind_param("ii", $userId, $entryId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Count the total number of votes for a specific entry.
     * 
     * @param int $entryId The ID of the competition entry.
     * @return int The total number of votes for the entry.
     * Example: $vote->countVotes(10);
     */
    public function countVotes($entryId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS vote_count FROM {$this->table} WHERE entry_id = ?");
        $stmt->bind_param("i", $entryId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['vote_count'] ?? 0;
    }

    /**
     * Remove a vote for a specific entry (optional feature).
     * 
     * @param int $userId The ID of the user.
     * @param int $entryId The ID of the competition entry.
     * @return bool True if the vote was successfully removed, false otherwise.
     * Example: $vote->removeVote(1, 10);
     */
    public function removeVote($userId, $entryId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND entry_id = ?");
        $stmt->bind_param("ii", $userId, $entryId);
        return $stmt->execute();
    }
}
?>

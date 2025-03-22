<?php
// controllers/VoteController.php
require_once '../models/Vote.php';
require_once 'AuthController.php';
require_once '../db.php';

class VoteController {
    private $db;
    private $auth;
    private $voteModel;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new AuthController($db);
        $this->voteModel = new Vote($db);
    }

    // Cast a vote for a competition entry
    public function castVote($entryId) {
        if (!$this->auth->isAuthenticated()) {
            return "User not authenticated.";
        }

        $userId = $this->auth->getUserId();
        
        // Check if the user has already voted
        if ($this->voteModel->hasUserVoted($userId, $entryId)) {
            return "You have already voted for this entry.";
        }

        // Record the vote
        if ($this->voteModel->addVote($userId, $entryId)) {
            return "Vote cast successfully.";
        }

        return "Failed to cast vote.";
    }

    // Get total votes for a competition entry
    public function getVotesForEntry($entryId) {
        return $this->voteModel->countVotes($entryId);
    }
}
?>

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

    /**
     * Cast a vote for a competition entry.
     * 
     * @param int $entryId The ID of the competition entry to vote for.
     * @return string A message indicating the result of the vote operation.
     * Example: $voteController->castVote(10);
     */
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

    /**
     * Get the total number of votes for a competition entry.
     * 
     * @param int $entryId The ID of the competition entry.
     * @return int The total number of votes for the entry.
     * Example: $voteController->getVotesForEntry(10);
     */
    public function getVotesForEntry($entryId) {
        return $this->voteModel->countVotes($entryId);
    }

    /**
     * Prepare the vote page by fetching entries for a specific competition.
     * 
     * @param int $competitionId The ID of the competition.
     * @return void
     * Example: $voteController->prepareVotePage(1);
     */
    public function prepareVotePage($competitionId) {
        $entryController = new EntryController($this->db);
        $entries = $entryController->getEntriesByCompetition($competitionId);
        $_SESSION['entries'] = $entries;
    }
}
?>

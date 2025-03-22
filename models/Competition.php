<?php
require_once __DIR__ . '/../db.php';

class Competition {
    private $db;
    private $table = 'Competitions';

    public function __construct($db) {
        $this->db = $db;
    }

    // Create a new competition
    public function createCompetition($title, $description, $start_date, $end_date) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (title, description, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $start_date, $end_date);
        return $stmt->execute();
    }

    // Get all competitions
    public function getAllCompetitions() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY start_date DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get a specific competition by ID
    public function getCompetitionById($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update competition details
    public function updateCompetition($competitionId, $title, $description, $start_date, $end_date) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET title = ?, description = ?, start_date = ?, end_date = ? WHERE competition_id = ?");
        $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $competitionId);
        return $stmt->execute();
    }

    // Delete a competition
    public function deleteCompetition($competitionId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        return $stmt->execute();
    }

    // Get all competitions
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get competition by ID
    public function getById($competitionId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>

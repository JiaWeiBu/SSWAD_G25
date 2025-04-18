<?php
require_once 'db.php'; // Include database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $competitionId = $_POST['competition_id'] ?? null;

    if (!$competitionId) {
        http_response_code(400);
        echo "Invalid competition ID.";
        exit();
    }

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo "You must be logged in to delete a competition.";
        exit();
    }

    // Verify if the logged-in user is the creator of the competition
    $stmt = $conn->prepare("SELECT user_id FROM Competitions WHERE id = ?");
    $stmt->bind_param("i", $competitionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || $result['user_id'] !== $_SESSION['user_id']) {
        http_response_code(403);
        echo "You are not authorized to delete this competition.";
        exit();
    }

    // Delete the competition and related entries
    $conn->begin_transaction();
    try {
        // Delete competition entries
        $stmt = $conn->prepare("DELETE FROM competition_entries WHERE competition_id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();

        // Delete the competition itself
        $stmt = $conn->prepare("DELETE FROM Competitions WHERE id = ?");
        $stmt->bind_param("i", $competitionId);
        $stmt->execute();

        $conn->commit();
        echo "Competition deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo "An error occurred while deleting the competition.";
    }
} else {
    http_response_code(405);
    echo "Invalid request method.";
}
?>

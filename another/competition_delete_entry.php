<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entryId = $_POST['entry_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if (!$entryId || !$userId) {
        http_response_code(400);
        echo 'Invalid request.';
        exit();
    }

    // Verify the user is the author of the entry
    $stmt = $db->prepare("SELECT id FROM competition_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entryId, $userId);
    $stmt->execute();
    $entry = $stmt->get_result()->fetch_assoc();

    if (!$entry) {
        http_response_code(403);
        echo 'You are not authorized to delete this entry.';
        exit();
    }

    // Delete the entry
    $stmt = $db->prepare("DELETE FROM competition_entries WHERE id = ?");
    $stmt->bind_param("i", $entryId);
    $stmt->execute();

    echo 'Entry deleted successfully.';
}
?>

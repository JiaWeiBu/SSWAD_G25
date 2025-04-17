<?php
/**
 * This file handles the deletion of a competition comment.
 * It ensures the user is authorized to delete the comment before performing the deletion.
 * 
 * Key Features:
 * - Validates the comment ID and user ID.
 * - Verifies that the user is the author of the comment.
 * - Deletes the comment from the database.
 */

require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = $_POST['comment_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if (!$commentId || !$userId) {
        http_response_code(400);
        echo 'Invalid request.';
        exit();
    }

    // Verify the user is the author of the comment
    $stmt = $conn->prepare("SELECT id FROM competition_entries_comments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $commentId, $userId);
    $stmt->execute();
    $comment = $stmt->get_result()->fetch_assoc();

    if (!$comment) {
        http_response_code(403);
        echo 'You are not authorized to delete this comment.';
        exit();
    }

    // Delete the comment
    $stmt = $conn->prepare("DELETE FROM competition_entries_comments WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();

    echo 'Comment deleted successfully.';
}
?>

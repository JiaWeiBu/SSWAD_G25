<?php
/**
 * This file handles liking or unliking a competition entry.
 * It ensures the user is logged in and updates the like status in the database.
 * 
 * Key Features:
 * - Validates user authentication.
 * - Toggles the like status for a competition entry.
 * - Redirects to the competition details page.
 */

require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$entryId = $_POST['entry_id'] ?? null;

if ($entryId) {
    // Check if the user has already liked the entry
    $stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND entry_id = ?");
    $stmt->bind_param("ii", $userId, $entryId);
    $stmt->execute();
    $existingLike = $stmt->get_result()->fetch_assoc();

    if ($existingLike) {
        // Unlike the entry
        $stmt = $db->prepare("DELETE FROM likes WHERE id = ?");
        $stmt->bind_param("i", $existingLike['id']);
        $stmt->execute();
    } else {
        // Like the entry
        $stmt = $db->prepare("INSERT INTO likes (user_id, entry_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $entryId);
        $stmt->execute();
    }
}

header("Location: competition_details.php?id=" . $_GET['competition_id']);
exit();
?>

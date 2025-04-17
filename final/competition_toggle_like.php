<?php
/**
 * This file handles toggling the like status of a competition entry.
 * It ensures the user is logged in and updates the like status in the database.
 * 
 * Key Features:
 * - Validates user authentication.
 * - Toggles the like status for a competition entry.
 * - Returns a success or error response.
 */
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$userId = $_SESSION['user_id'];
$entryId = $_POST['entry_id'] ?? null;
$isLiked = $_POST['is_liked'] ?? null;

if (!$entryId || !isset($isLiked)) {
    http_response_code(400);
    echo "Bad Request";
    exit();
}

if ($isLiked === 'true') {
    // Unlike the entry
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND entry_id = ?");
    $stmt->bind_param("ii", $userId, $entryId);
} else {
    // Like the entry
    $stmt = $conn->prepare("INSERT INTO likes (user_id, entry_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $entryId);
}

if ($stmt->execute()) {
    http_response_code(200);
    echo "Success";
} else {
    http_response_code(500);
    echo "Error";
}

// JavaScript function to toggle like
?>
<script>
function toggleLike(entryId, isLiked) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'competition_toggle_like.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                console.log('Success:', xhr.responseText);
            } else {
                console.error('Error:', xhr.responseText);
            }
        }
    };
    xhr.send(`entry_id=${entryId}&is_liked=${isLiked}`);
}
</script>

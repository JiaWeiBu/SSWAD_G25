<?php
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
    $stmt = $db->prepare("DELETE FROM likes WHERE user_id = ? AND entry_id = ?");
    $stmt->bind_param("ii", $userId, $entryId);
} else {
    // Like the entry
    $stmt = $db->prepare("INSERT INTO likes (user_id, entry_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $entryId);
}

if ($stmt->execute()) {
    http_response_code(200);
    echo "Success";
} else {
    http_response_code(500);
    echo "Error";
}
?>

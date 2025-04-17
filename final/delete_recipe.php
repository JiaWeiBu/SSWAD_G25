<?php
include 'db.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Invalid recipe ID.");
}

$recipe_id = $_GET['id'];

// verify ownership
$stmt = $conn->prepare("SELECT image, user_id FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Recipe not found.");
}
$recipe = $result->fetch_assoc();

if ($recipe['user_id'] != $_SESSION['user_id']) {
    die("Unauthorized access.");
}



try {
    $conn->begin_transaction();

    // Delete image if exists
    if (!empty($recipe['image']) && file_exists($recipe['image'])) {
        unlink($recipe['image']);
    }
    
    $conn->query("DELETE FROM ingredients WHERE recipe_id = $recipe_id");
    $conn->query("DELETE FROM steps WHERE recipe_id = $recipe_id");
    $conn->query("DELETE FROM recipes WHERE id = $recipe_id");
    
    $conn->commit();
    http_response_code(200); // Success
    exit();

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    exit("Deletion failed");
}


?>

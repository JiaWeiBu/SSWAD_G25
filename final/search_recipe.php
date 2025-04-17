<?php
include 'db.php';
session_start();



// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify authentication
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

// Get parameters
$searchRaw = isset($_GET['query']) ? trim($_GET['query']) : '';
$cuisineFilter = isset($_GET['cuisine']) ? trim($_GET['cuisine']) : '';

// Prepare SQL
$sql = "SELECT id, name, cuisine, image FROM recipes WHERE user_id = ?";
$params = [$_SESSION['user_id']];
$types = "i";

// Add search conditions
if (!empty($searchRaw)) {
    $sql .= " AND (name LIKE ? OR cuisine LIKE ?)";
    $types .= "ss";
    $searchParam = "%" . $conn->real_escape_string($searchRaw) . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Add cuisine filter
if (!empty($cuisineFilter)) {
    $sql .= " AND cuisine = ?";
    $types .= "s";
    $params[] = $conn->real_escape_string($cuisineFilter);
}

// Execute query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="recipe-card">';
        echo '<div class="recipe-card-img">';
        if ($row['image']) {
            echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Recipe Image">';
        } else {
            echo '<img src="default-image.jpg" alt="Default Recipe Image">';
        }
        echo '</div>';
        echo '<div class="recipe-card-details">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p><strong>Cuisine:</strong> ' . htmlspecialchars($row['cuisine']) . '</p>';
        echo '<a href="view_recipe.php?id=' . $row['id'] . '" class="view-recipe-btn">View Recipe</a>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p style="text-align:center; width: 100%;">No recipes found.</p>';
}
?>

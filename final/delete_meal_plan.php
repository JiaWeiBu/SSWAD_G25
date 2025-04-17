<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$plan_id = isset($_GET['plan_id']) ? trim($_GET['plan_id']) : '';

if (empty($plan_id) || !is_numeric($plan_id)) {
    header("Location: view_meal_plans.php?error=" . urlencode("Invalid plan ID."));
    exit();
}

try {
    // Delete the specific plan
    $stmt = $conn->prepare("DELETE FROM meal_plans WHERE plan_id = ? AND user_id = ?");
    $stmt->execute([$plan_id, $user_id]);

    $rowCount = $stmt->rowCount();
    if ($rowCount === 0) {
        header("Location: view_meal_plans.php?error=" . urlencode("No meal plan found with ID $plan_id for this user."));
        exit();
    }

    header("Location: view_meal_plans.php?success=1");
} catch (PDOException $e) {
    header("Location: view_meal_plans.php?error=" . urlencode("Error deleting meal plan: " . $e->getMessage()));
}
?>
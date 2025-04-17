<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = $_POST['plan_id'] ?? '';
    $meal_date_start = $_POST['meal_date'] ?? '';
    $meal_date_end = $_POST['meal_date_end'] ?? '';
    $meal_type = $_POST['meal_type'] ?? '';
    $meal_option = $_POST['meal_option'] ?? 'recipe';
    $recipe_id = $_POST['recipe_id'] ?? null;
    $custom_meal_name = $_POST['custom_meal_name'] ?? null;
    $custom_meal_ingredients = $_POST['custom_meal_ingredients'] ?? null; // New field
    $notes = $_POST['notes'] ?? '';

    // Validation
    if (empty($meal_date_start) || empty($meal_date_end) || empty($meal_type)) {
        header("Location: meal_planning.php?error=" . urlencode("All required fields must be filled."));
        exit();
    }

    // Validate date range
    $start_date = new DateTime($meal_date_start);
    $end_date = new DateTime($meal_date_end);
    if ($start_date > $end_date) {
        header("Location: meal_planning.php?error=" . urlencode("End date must be on or after start date."));
        exit();
    }

    // Validate meal selection
    if ($meal_option === 'recipe' && empty($recipe_id)) {
        header("Location: meal_planning.php?error=" . urlencode("Please select a recipe."));
        exit();
    } elseif ($meal_option === 'custom' && (empty($custom_meal_name) || empty($custom_meal_ingredients))) {
        header("Location: meal_planning.php?error=" . urlencode("Please enter both a custom meal name and ingredients."));
        exit();
    }

    try {
        // If editing (plan_id exists), delete existing plans for this plan_id (simplified approach)
        if (!empty($plan_id)) {
            $stmt = $conn->prepare("DELETE FROM meal_plans WHERE plan_id = ? AND user_id = ?");
            $stmt->execute([$plan_id, $user_id]);
        }

        // Loop through each day in the date range
        $interval = new DateInterval('P1D'); // 1 day interval
        $date_period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day')); // Include end date

        foreach ($date_period as $date) {
            $current_date = $date->format('Y-m-d');

            // Insert meal plan for each day
            $stmt = $conn->prepare("INSERT INTO meal_plans (user_id, meal_date, meal_type, recipe_id, custom_meal_name, custom_meal_ingredients, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $current_date,
                $meal_type,
                $meal_option === 'recipe' ? $recipe_id : null,
                $meal_option === 'custom' ? $custom_meal_name : null,
                $meal_option === 'custom' ? $custom_meal_ingredients : null, // Save ingredients
                $notes
            ]);
        }

        header("Location: view_meal_plans.php?success=1");
        exit();
    } catch (PDOException $e) {
        header("Location: meal_planning.php?error=" . urlencode("Error saving meal plan: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: meal_planning.php?error=" . urlencode("Invalid request method."));
    exit();
}
?>
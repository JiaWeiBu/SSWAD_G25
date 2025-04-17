<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch available recipes for selection
try {
    $stmt = $conn->prepare("SELECT id, name FROM recipes"); // Changed 'title' to 'name'
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching recipes: " . htmlspecialchars($e->getMessage());
    $recipes = [];
}

// Fetch saved meal plans for the user
try {
    $stmt = $conn->prepare("
        SELECT saved_plan_id, meal_type, recipe_id, custom_meal_name, custom_meal_ingredients, notes
        FROM saved_meal_plans
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $saved_meal_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching saved meal plans: " . htmlspecialchars($e->getMessage());
    $saved_meal_plans = [];
}

// Pre-fill form if editing or loading a saved meal plan
$plan_id = $_GET['plan_id'] ?? '';
$meal_date = $_GET['meal_date'] ?? '';
$meal_date_end = $_GET['meal_date_end'] ?? $meal_date;
$meal_type = $_GET['meal_type'] ?? '';
$meal_option = $_GET['meal_option'] ?? 'recipe';
$custom_meal_name = $_GET['custom_meal_name'] ?? '';
$custom_meal_ingredients = $_GET['custom_meal_ingredients'] ?? '';
$recipe_id = $_GET['recipe_id'] ?? '';
$notes = $_GET['notes'] ?? '';
$save_for_future = $_GET['save_for_future'] ?? '0';
$saved_plan_id = $_POST['saved_plan_id'] ?? $_GET['saved_plan_id'] ?? '';

// Flag to indicate if a saved meal plan is being used
$using_saved_plan = !empty($saved_plan_id);

// Load saved meal plan if selected
if ($saved_plan_id && !$plan_id) {
    foreach ($saved_meal_plans as $saved_plan) {
        if ($saved_plan['saved_plan_id'] == $saved_plan_id) {
            $meal_type = $saved_plan['meal_type'];
            $meal_option = $saved_plan['recipe_id'] ? 'recipe' : 'custom';
            $recipe_id = $saved_plan['recipe_id'];
            $custom_meal_name = $saved_plan['custom_meal_name'];
            $custom_meal_ingredients = $saved_plan['custom_meal_ingredients'];
            $notes = $saved_plan['notes'];
            break;
        }
    }
}

// If editing, fetch existing meal plan
if ($plan_id) {
    try {
        $stmt = $conn->prepare("
            SELECT meal_date, meal_type, recipe_id, custom_meal_name, custom_meal_ingredients, notes
            FROM meal_plans
            WHERE plan_id = ? AND user_id = ?
        ");
        $stmt->execute([$plan_id, $user_id]);
        $meal_plan = $stmt->fetch(); // Corrected: Removed the argument

        if ($meal_plan) {
            $meal_date = $meal_plan['meal_date'];
            $meal_date_end = $meal_plan['meal_date'];
            $meal_type = $meal_plan['meal_type'];
            $meal_option = $meal_plan['recipe_id'] ? 'recipe' : 'custom';
            $recipe_id = $meal_plan['recipe_id'];
            $custom_meal_name = $meal_plan['custom_meal_name'];
            $custom_meal_ingredients = $meal_plan['custom_meal_ingredients'];
            $notes = $meal_plan['notes'];
        } else {
            $error = "Meal plan not found or you don't have permission to edit it.";
        }
    } catch (PDOException $e) {
        $error = "Error fetching meal plan: " . htmlspecialchars($e->getMessage());
    }
}

// Handle form submission for saving the meal plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_meal_plan'])) {
    $meal_date = $_POST['meal_date'] ?? '';
    $meal_date_end = $_POST['meal_date_end'] ?? '';
    $meal_type = $_POST['meal_type'] ?? '';
    $meal_option = $_POST['meal_option'] ?? 'recipe';
    $recipe_id = $_POST['recipe_id'] ?? '';
    $custom_meal_name = $_POST['custom_meal_name'] ?? '';
    $custom_meal_ingredients = $_POST['custom_meal_ingredients'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $plan_id = $_POST['plan_id'] ?? '';
    $save_for_future = isset($_POST['save_for_future']) ? 1 : 0;
    $saved_plan_id = $_POST['saved_plan_id'] ?? '';
    $using_saved_plan = !empty($saved_plan_id);

    // Validation
    if (empty($meal_date) || empty($meal_date_end)) {
        $error = "Please specify the date range.";
    } elseif (!$using_saved_plan && (empty($meal_type) || ($meal_option === 'recipe' && empty($recipe_id)) || ($meal_option === 'custom' && (empty($custom_meal_name) || empty($custom_meal_ingredients))))) {
        $error = "Please fill in all required fields.";
    } else {
        // Convert date range to DateTime objects
        $start_date = new DateTime($meal_date);
        $end_date = new DateTime($meal_date_end);
        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));

        try {
            // Begin transaction
            $conn->beginTransaction();

            // Save to meal_plans table
            foreach ($date_range as $date) {
                $current_date = $date->format('Y-m-d');

                // Prepare data for insertion/update
                $recipe_id_to_save = $meal_option === 'recipe' ? $recipe_id : null;
                $custom_meal_name_to_save = $meal_option === 'custom' ? $custom_meal_name : null;
                $custom_meal_ingredients_to_save = $meal_option === 'custom' ? $custom_meal_ingredients : null;
                $notes_to_save = !empty($notes) ? $notes : null;

                if ($plan_id) {
                    // Update existing meal plan
                    $stmt = $conn->prepare("
                        UPDATE meal_plans
                        SET meal_date = ?, meal_type = ?, recipe_id = ?, custom_meal_name = ?, custom_meal_ingredients = ?, notes = ?
                        WHERE plan_id = ? AND user_id = ?
                    ");
                    $stmt->execute([
                        $current_date,
                        $meal_type,
                        $recipe_id_to_save,
                        $custom_meal_name_to_save,
                        $custom_meal_ingredients_to_save,
                        $notes_to_save,
                        $plan_id,
                        $user_id
                    ]);
                } else {
                    // Insert new meal plan
                    $stmt = $conn->prepare("
                        INSERT INTO meal_plans (user_id, meal_date, meal_type, recipe_id, custom_meal_name, custom_meal_ingredients, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user_id,
                        $current_date,
                        $meal_type,
                        $recipe_id_to_save,
                        $custom_meal_name_to_save,
                        $custom_meal_ingredients_to_save,
                        $notes_to_save
                    ]);
                }
            }

            // Save for future use if checked (only when not using a saved plan)
            if ($save_for_future && !$using_saved_plan) {
                $stmt = $conn->prepare("
                    INSERT INTO saved_meal_plans (user_id, meal_type, recipe_id, custom_meal_name, custom_meal_ingredients, notes)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user_id,
                    $meal_type,
                    $recipe_id_to_save,
                    $custom_meal_name_to_save,
                    $custom_meal_ingredients_to_save,
                    $notes_to_save
                ]);
            }

            // Commit transaction
            $conn->commit();

            // Redirect to view_meal_plans.php with a success message
            header("Location: view_meal_plans.php?success=Meal+plan+saved+successfully");
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Error saving meal plan: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planning - Recipe App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="meal_style.css">
</head>
<body>
    <div class="wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">Recipe App</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="recipes.php">Recipes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="meal_planning.php">Meal Planning</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="view_meal_plans.php">View Meal Plan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="community.php">Community</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="competitions.php">Competitions</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <span class="nav-link">Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="content">
            <main>
                <h2><?php echo $plan_id ? 'Edit Meal Plan' : 'Plan a Meal'; ?></h2>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <form method="POST" id="saved_plan_form" action="meal_planning.php">
                    <input type="hidden" name="saved_plan_id" id="saved_plan_id" value="<?php echo htmlspecialchars($saved_plan_id); ?>">
                    <div class="form-group">
                        <label for="saved_plan_id_select">Load Saved Meal Plan (optional):</label>
                        <select id="saved_plan_id_select" name="saved_plan_id_select">
                            <option value="" <?php echo empty($saved_plan_id) ? 'selected' : ''; ?>>Select a saved meal plan</option>
                            <?php foreach ($saved_meal_plans as $saved_plan): ?>
                                <option value="<?php echo $saved_plan['saved_plan_id']; ?>" <?php echo $saved_plan_id == $saved_plan['saved_plan_id'] ? 'selected' : ''; ?>>
                                    <?php 
                                    $label = $saved_plan['meal_type'];
                                    if ($saved_plan['recipe_id']) {
                                        $recipe = array_filter($recipes, function($r) use ($saved_plan) {
                                            return $r['id'] == $saved_plan['recipe_id'];
                                        });
                                        $recipe = reset($recipe);
                                        $label .= ' - ' . ($recipe ? htmlspecialchars($recipe['name']) : 'Unknown Recipe'); // Changed 'title' to 'name'
                                    } else {
                                        $label .= ' - ' . htmlspecialchars($saved_plan['custom_meal_name']);
                                    }
                                    echo $label;
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <form method="POST" id="meal_plan_form">
                    <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan_id); ?>">
                    <input type="hidden" name="saved_plan_id" value="<?php echo htmlspecialchars($saved_plan_id); ?>">
                    <input type="hidden" name="save_meal_plan" value="1">
                    <div class="form-group">
                        <label for="meal_date">Date Range Start:</label>
                        <input type="date" id="meal_date" name="meal_date" value="<?php echo htmlspecialchars($meal_date); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="meal_date_end">Date Range End:</label>
                        <input type="date" id="meal_date_end" name="meal_date_end" value="<?php echo htmlspecialchars($meal_date_end); ?>" required>
                    </div>
                    <div id="meal_details" style="display: <?php echo $using_saved_plan ? 'none' : 'block'; ?>;">
                        <div class="form-group">
                            <label for="meal_type">Meal Type:</label>
                            <select id="meal_type" name="meal_type" <?php echo $using_saved_plan ? '' : 'required'; ?>>
                                <option value="" disabled <?php echo empty($meal_type) ? 'selected' : ''; ?>>Select a meal type</option>
                                <option value="Breakfast" <?php echo $meal_type === 'Breakfast' ? 'selected' : ''; ?>>Breakfast</option>
                                <option value="Lunch" <?php echo $meal_type === 'Lunch' ? 'selected' : ''; ?>>Lunch</option>
                                <option value="Dinner" <?php echo $meal_type === 'Dinner' ? 'selected' : ''; ?>>Dinner</option>
                                <option value="Supper" <?php echo $meal_type === 'Supper' ? 'selected' : ''; ?>>Supper</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Meal Option:</label>
                            <input type="radio" id="recipe_option" name="meal_option" value="recipe" <?php echo $meal_option === 'recipe' ? 'checked' : ''; ?> onchange="toggleMealFields()">
                            <label for="recipe_option">Select Recipe</label>
                            <input type="radio" id="custom_option" name="meal_option" value="custom" <?php echo $meal_option === 'custom' ? 'checked' : ''; ?> onchange="toggleMealFields()">
                            <label for="custom_option">Custom Meal</label>
                        </div>
                        <div id="recipe_fields" class="form-group" style="display: <?php echo $meal_option === 'recipe' ? 'block' : 'none'; ?>;">
                            <label for="recipe_id">Select Recipe:</label>
                            <select id="recipe_id" name="recipe_id" <?php echo $meal_option === 'recipe' && !$using_saved_plan ? 'required' : ''; ?>>
                                <option value="" disabled <?php echo empty($recipe_id) ? 'selected' : ''; ?>>Select a recipe</option>
                                <?php foreach ($recipes as $recipe): ?>
                                    <option value="<?php echo $recipe['id']; ?>" <?php echo $recipe_id == $recipe['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($recipe['name']); // Changed 'title' to 'name' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="custom_fields" class="form-group" style="display: <?php echo $meal_option === 'custom' ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label for="custom_meal_name">Custom Meal Name:</label>
                                <input type="text" id="custom_meal_name" name="custom_meal_name" value="<?php echo htmlspecialchars($custom_meal_name); ?>" <?php echo $meal_option === 'custom' && !$using_saved_plan ? 'required' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="custom_meal_ingredients">Custom Meal Ingredients:</label>
                                <textarea id="custom_meal_ingredients" name="custom_meal_ingredients" <?php echo $meal_option === 'custom' && !$using_saved_plan ? 'required' : ''; ?>><?php echo htmlspecialchars($custom_meal_ingredients); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes (optional):</label>
                            <textarea id="notes" name="notes"><?php echo htmlspecialchars($notes); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="save_for_future" value="1" <?php echo $save_for_future ? 'checked' : ''; ?>>
                                Save for Future Use
                            </label>
                        </div>
                    </div>
                    <?php if ($using_saved_plan): ?>
                        <div class="saved-plan-details">
                            <h3>Saved Meal Plan Details:</h3>
                            <p><strong>Meal Type:</strong> <?php echo htmlspecialchars($meal_type); ?></p>
                            <p><strong>Meal:</strong> 
                                <?php
                                if ($meal_option === 'recipe') {
                                    $recipe = array_filter($recipes, function($r) use ($recipe_id) {
                                        return $r['id'] == $recipe_id;
                                    });
                                    $recipe = reset($recipe);
                                    echo htmlspecialchars($recipe ? $recipe['name'] : 'Unknown Recipe'); // Changed 'title' to 'name'
                                } else {
                                    echo htmlspecialchars($custom_meal_name);
                                }
                                ?>
                            </p>
                            <?php if (!empty($notes)): ?>
                                <p><strong>Notes:</strong> <?php echo htmlspecialchars($notes); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <button type="submit"><?php echo $plan_id ? 'Update Meal Plan' : 'Save Meal Plan'; ?></button>
                </form>
            </main>
        </div>
    </div>
    <footer class="bg-dark text-white py-2">
        <div class="container text-center">
            <p>© 2025 Recipe App | UCCD3243 Server-Side Web Applications Development</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMealFields() {
            try {
                const selectRecipe = document.getElementById('recipe_option').checked;
                document.getElementById('recipe_fields').style.display = selectRecipe ? 'block' : 'none';
                document.getElementById('custom_fields').style.display = selectRecipe ? 'none' : 'block';
                document.getElementById('recipe_id').required = selectRecipe && !<?php echo json_encode($using_saved_plan); ?>;
                document.getElementById('custom_meal_name').required = !selectRecipe && !<?php echo json_encode($using_saved_plan); ?>;
                document.getElementById('custom_meal_ingredients').required = !selectRecipe && !<?php echo json_encode($using_saved_plan); ?>;
            } catch (error) {
                console.error('Error in toggleMealFields:', error);
            }
        }

        document.getElementById('recipe_option')?.addEventListener('change', toggleMealFields);
        document.getElementById('custom_option')?.addEventListener('change', toggleMealFields);
        toggleMealFields();

        // Update saved_plan_id hidden fields and submit the form
        document.getElementById('saved_plan_id_select').addEventListener('change', function() {
            document.getElementById('saved_plan_id').value = this.value;
            document.getElementById('saved_plan_form').submit();
        });
    </script>
</body>
</html>
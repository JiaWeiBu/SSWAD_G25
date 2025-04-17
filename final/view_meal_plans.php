<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle success or error messages
$success = isset($_GET['success']) ? "Meal plan updated successfully!" : '';
$error = '';

// Handle grouping and sorting parameters
$group_by = $_GET['group_by'] ?? 'Day'; // Default to 'Day'
$sort_by = $_GET['sort_by'] ?? 'Nearest'; // Default to 'Nearest'

// Validate group_by and sort_by
$valid_groups = ['Day', 'Week', 'Month', 'Year'];
$valid_sorts = ['Ascending', 'Descending', 'Nearest'];
if (!in_array($group_by, $valid_groups)) $group_by = 'Day';
if (!in_array($sort_by, $valid_sorts)) $sort_by = 'Nearest';

// Handle week navigation for "Group by: Day"
$week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d'); // Default to today (April 13, 2025)
$week_start_date = new DateTime($week_start);
$week_end_date = clone $week_start_date;
$week_end_date->modify('+6 days'); // Set to 6 days after the start date (April 19, 2025)

// Calculate previous and next week for navigation
$prev_week = (clone $week_start_date)->modify('-7 days')->format('Y-m-d');
$next_week = (clone $week_start_date)->modify('+7 days')->format('Y-m-d');

// Format the week label (e.g., "Week of April 13, 2025")
$week_label = 'Week of ' . $week_start_date->format('F d, Y');

// Define grouping logic for SQL
$group_expression = '';
$group_label = '';
$where_clause = '';
if ($group_by === 'Day') {
    $group_expression = 'DATE(mp.meal_date)';
    $group_label = 'CONCAT(DAYNAME(mp.meal_date), ", ", DATE_FORMAT(mp.meal_date, "%b %d, %Y"))';
    $where_clause = "AND mp.meal_date BETWEEN ? AND ?";
} else {
    switch ($group_by) {
        case 'Week':
            $group_expression = 'YEARWEEK(mp.meal_date, 1)';
            $group_label = 'CONCAT(YEAR(mp.meal_date), "-W", LPAD(WEEK(mp.meal_date, 1), 2, "0"))';
            break;
        case 'Month':
            $group_expression = 'DATE_FORMAT(mp.meal_date, "%Y-%m")';
            $group_label = 'DATE_FORMAT(mp.meal_date, "%Y-%m")';
            break;
        case 'Year':
            $group_expression = 'YEAR(mp.meal_date)';
            $group_label = 'YEAR(mp.meal_date)';
            break;
    }
    $where_clause = '';
}

// Define sorting logic
$order_by = '';
switch ($sort_by) {
    case 'Ascending':
        $order_by = 'mp.meal_date ASC, mp.meal_type ASC';
        break;
    case 'Descending':
        $order_by = 'mp.meal_date DESC, mp.meal_type DESC';
        break;
    case 'Nearest':
        $order_by = 'ABS(DATEDIFF(mp.meal_date, CURDATE())) ASC, mp.meal_type ASC';
        break;
}

// Fetch meal plans with grouping
$meal_plans_by_group = [];
try {
    $query = "
        SELECT 
            $group_label AS group_key,
            mp.plan_id, 
            mp.meal_date, 
            mp.meal_type, 
            mp.recipe_id, 
            mp.custom_meal_name, 
            mp.custom_meal_ingredients, 
            mp.notes, 
            r.name AS recipe_name
        FROM meal_plans mp
        LEFT JOIN recipes r ON mp.recipe_id = r.id
        WHERE mp.user_id = ?
        $where_clause
        ORDER BY $order_by
    ";
    
    $stmt = $conn->prepare($query);
    if ($group_by === 'Day') {
        $stmt->execute([
            $user_id,
            $week_start_date->format('Y-m-d'),
            $week_end_date->format('Y-m-d')
        ]);
    } else {
        $stmt->execute([$user_id]);
    }
    $meal_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group the meal plans in PHP
    foreach ($meal_plans as $plan) {
        $group_key = $plan['group_key'];
        if (!isset($meal_plans_by_group[$group_key])) {
            $meal_plans_by_group[$group_key] = [];
        }
        $meal_plans_by_group[$group_key][] = $plan;
    }
} catch (PDOException $e) {
    $error = "Error fetching meal plans: " . htmlspecialchars($e->getMessage());
    $meal_plans_by_group = [];
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM meal_plans WHERE plan_id = ? AND user_id = ?");
        $stmt->execute([$delete_id, $user_id]);
        header("Location: view_meal_plans.php?success=Meal plan deleted successfully!&group_by=$group_by&sort_by=$sort_by&week_start=$week_start");
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting meal plan: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Meal Plans - Recipe App</title>
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
                            <a class="nav-link" href="meal_planning.php">Meal Planning</a>
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
                <h2>Your Meal Plans</h2>
                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>

                <div class="group-options">
                    <span>Group by:</span>
                    <a href="?group_by=Day&sort_by=<?php echo $sort_by; ?>&week_start=<?php echo $week_start; ?>" class="<?php echo $group_by === 'Day' ? 'active' : ''; ?>">Day</a>
                    <a href="?group_by=Week&sort_by=<?php echo $sort_by; ?>" class="<?php echo $group_by === 'Week' ? 'active' : ''; ?>">Week</a>
                    <a href="?group_by=Month&sort_by=<?php echo $sort_by; ?>" class="<?php echo $group_by === 'Month' ? 'active' : ''; ?>">Month</a>
                    <a href="?group_by=Year&sort_by=<?php echo $sort_by; ?>" class="<?php echo $group_by === 'Year' ? 'active' : ''; ?>">Year</a>
                </div>

                <div class="sort-options">
                    <span>Sort by:</span>
                    <a href="?group_by=<?php echo $group_by; ?>&sort_by=Ascending&week_start=<?php echo $week_start; ?>" class="<?php echo $sort_by === 'Ascending' ? 'active' : ''; ?>">Ascending</a>
                    <a href="?group_by=<?php echo $group_by; ?>&sort_by=Descending&week_start=<?php echo $week_start; ?>" class="<?php echo $sort_by === 'Descending' ? 'active' : ''; ?>">Descending</a>
                    <a href="?group_by=<?php echo $group_by; ?>&sort_by=Nearest&week_start=<?php echo $week_start; ?>" class="<?php echo $sort_by === 'Nearest' ? 'active' : ''; ?>">Nearest to Current Date</a>
                </div>

                <?php if ($group_by === 'Day'): ?>
                    <div class="week-navigation">
                        <a href="?group_by=Day&sort_by=<?php echo $sort_by; ?>&week_start=<?php echo $prev_week; ?>">←</a>
                        <span><?php echo $week_label; ?> to <?php echo $week_end_date->format('F d, Y'); ?></span>
                        <a href="?group_by=Day&sort_by=<?php echo $sort_by; ?>&week_start=<?php echo $next_week; ?>">→</a>
                    </div>
                <?php endif; ?>

                <?php if (empty($meal_plans_by_group)): ?>
                    <p>No meal plans found for this period. <a href="meal_planning.php">Create a new meal plan</a>.</p>
                <?php else: ?>
                    <?php foreach ($meal_plans_by_group as $group_key => $plans): ?>
                        <h3><?php echo htmlspecialchars($group_key); ?></h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Meal Type</th>
                                    <th>Meal</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($plans as $plan): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($plan['meal_date']); ?></td>
                                        <td><?php echo htmlspecialchars($plan['meal_type']); ?></td>
                                        <td>
                                            <?php 
                                            if ($plan['recipe_id'] && $plan['recipe_name']) {
                                                echo htmlspecialchars($plan['recipe_name']);
                                            } elseif ($plan['custom_meal_name']) {
                                                echo htmlspecialchars($plan['custom_meal_name']);
                                            } else {
                                                echo "N/A";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($plan['notes'] ?? ''); ?></td>
                                        <td>
                                            <a href="view_meal_details.php?plan_id=<?php echo $plan['plan_id']; ?>">View</a>
                                            <a href="meal_planning.php?plan_id=<?php echo $plan['plan_id']; ?>&meal_date=<?php echo $plan['meal_date']; ?>&meal_date_end=<?php echo $plan['meal_date']; ?>&meal_type=<?php echo $plan['meal_type']; ?>&meal_option=<?php echo $plan['recipe_id'] ? 'recipe' : 'custom'; ?>&recipe_id=<?php echo $plan['recipe_id']; ?>&custom_meal_name=<?php echo urlencode($plan['custom_meal_name']); ?>&custom_meal_ingredients=<?php echo urlencode($plan['custom_meal_ingredients']); ?>&notes=<?php echo urlencode($plan['notes']); ?>">Edit</a>
                                            <a href="view_meal_plans.php?delete_id=<?php echo $plan['plan_id']; ?>&group_by=<?php echo $group_by; ?>&sort_by=<?php echo $sort_by; ?>&week_start=<?php echo $week_start; ?>" onclick="return confirm('Are you sure you want to delete this meal plan?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php endif; ?>
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
</body>
</html>
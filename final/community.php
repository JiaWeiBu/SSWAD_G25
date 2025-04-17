<?php
session_start();

// Set the PHP time zone
date_default_timezone_set('Asia/Kuala_Lumpur');

$conn = mysqli_connect("localhost", "root", "", "culinary_db");


// Debugging: Log the current PHP and MySQL time
error_log("PHP Time: " . date('Y-m-d H:i:s'));
$result = $conn->query("SELECT NOW() AS mysql_time");
$mysql_time = $result->fetch_assoc()['mysql_time'];
error_log("MySQL Time: " . $mysql_time);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Get current username if logged in
function getCurrentUsername()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : "Guest";
}

// Get current user ID if logged in
function getCurrentUserId()
{
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Check if the current user is an admin
function isAdmin($conn)
{
    if (!isLoggedIn()) {
        return false;
    }
    $user_id = getCurrentUserId();
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user && $user['role'] === 'admin';
}

// Share a recipe as a discussion
function shareRecipe($conn, $recipe_id, $user_id, $message)
{
    error_log("Attempting to share recipe $recipe_id for user $user_id");

    // Validate recipe_id and user_id
    if (!is_numeric($recipe_id) || !is_numeric($user_id)) {
        error_log("Invalid recipe_id or user_id: recipe_id=$recipe_id, user_id=$user_id");
        return ['success' => false, 'message' => 'Invalid input parameters.'];
    }

    // Update query to remove 'description' since it doesn't exist
    $check_sql = "SELECT id, name FROM recipes WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        error_log("Prepare failed: " . $conn->error);
        return ['success' => false, 'message' => 'Database error occurred.'];
    }

    $check_stmt->bind_param("ii", $recipe_id, $user_id);
    if (!$check_stmt->execute()) {
        error_log("Execute failed: " . $check_stmt->error);
        $check_stmt->close();
        return ['success' => false, 'message' => 'Error checking recipe.'];
    }

    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $recipe = $check_result->fetch_assoc();
        $title = "Shared Recipe: " . htmlspecialchars($recipe['name']);
        // Since there's no description column, use a default message if $message is empty
        $content = htmlspecialchars($message ?: "Check out this recipe!");

        $sql = "INSERT INTO discussions (user_id, title, content, created_at, recipe_id) 
                VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $check_stmt->close();
            return ['success' => false, 'message' => 'Database error occurred.'];
        }

        $stmt->bind_param("issi", $user_id, $title, $content, $recipe_id);
        if ($stmt->execute()) {
            $new_discussion_id = $stmt->insert_id;
            $stmt->close();
            $check_stmt->close();
            error_log("Recipe $recipe_id shared successfully as discussion $new_discussion_id");
            return [
                'success' => true,
                'message' => 'Recipe shared successfully!',
                'new_discussion_id' => $new_discussion_id,
                'title' => $title,
                'content' => $content,
                'username' => getCurrentUsername(),
                'created_at' => date('F j, Y, g:i a'),
                'recipe_id' => $recipe_id
            ];
        } else {
            $error = $stmt->error;
            error_log("Insert failed: " . $error);
            $stmt->close();
            $check_stmt->close();
            return ['success' => false, 'message' => 'Error sharing recipe: ' . $error];
        }
    } else {
        error_log("Recipe $recipe_id not found or not owned by user $user_id");
        $check_stmt->close();
        return ['success' => false, 'message' => 'Invalid recipe selected.'];
    }
}

// Handle filter selection
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';
$order_by = ($sort === 'rating') ? 'avg_rating DESC' : 'd.created_at DESC';

// Process AJAX requests
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $user_id = getCurrentUserId();

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to perform this action.']);
        exit;
    }

    if ($action === 'create_discussion') {
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);

        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
            exit;
        }

        $sql = "INSERT INTO discussions (user_id, title, content, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $title, $content);

        if ($stmt->execute()) {
            $new_discussion_id = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Discussion created successfully!',
                'new_discussion_id' => $new_discussion_id,
                'title' => $title,
                'content' => $content,
                'username' => getCurrentUsername(),
                'created_at' => date('F j, Y, g:i a')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    if ($action === 'add_comment') {
        $discussion_id = $_POST['discussion_id'];
        $comment = htmlspecialchars($_POST['comment']);

        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
            exit;
        }

        $sql = "INSERT INTO comments (discussion_id, user_id, content, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $discussion_id, $user_id, $comment);

        if ($stmt->execute()) {
            $new_comment_id = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Comment added successfully!',
                'new_comment_id' => $new_comment_id,
                'content' => $comment,
                'username' => getCurrentUsername(),
                'created_at' => date('F j, Y, g:i a')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    if ($action === 'add_rating') {
        $discussion_id = $_POST['discussion_id'];
        $rating = $_POST['rating'];

        $checkSql = "SELECT * FROM ratings WHERE discussion_id = ? AND user_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $discussion_id, $user_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $updateSql = "UPDATE ratings SET rating = ?, updated_at = NOW() WHERE discussion_id = ? AND user_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("iii", $rating, $discussion_id, $user_id);

            if ($updateStmt->execute()) {
                $avgRatingSql = "SELECT AVG(rating) AS avg_rating FROM ratings WHERE discussion_id = ?";
                $avgStmt = $conn->prepare($avgRatingSql);
                $avgStmt->bind_param("i", $discussion_id);
                $avgStmt->execute();
                $avgResult = $avgStmt->get_result();
                $avgRating = $avgResult->fetch_assoc()['avg_rating'];
                $avgStmt->close();

                echo json_encode([
                    'success' => true,
                    'message' => 'Rating updated successfully!',
                    'avg_rating' => round($avgRating, 1)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating rating: ' . $updateStmt->error]);
            }
            $updateStmt->close();
        } else {
            $insertSql = "INSERT INTO ratings (discussion_id, user_id, rating, created_at) 
                        VALUES (?, ?, ?, NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iii", $discussion_id, $user_id, $rating);

            if ($insertStmt->execute()) {
                $avgRatingSql = "SELECT AVG(rating) AS avg_rating FROM ratings WHERE discussion_id = ?";
                $avgStmt = $conn->prepare($avgRatingSql);
                $avgStmt->bind_param("i", $discussion_id);
                $avgStmt->execute();
                $avgResult = $avgStmt->get_result();
                $avgRating = $avgResult->fetch_assoc()['avg_rating'];
                $avgStmt->close();

                echo json_encode([
                    'success' => true,
                    'message' => 'Rating added successfully!',
                    'avg_rating' => round($avgRating, 1)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding rating: ' . $insertStmt->error]);
            }
            $insertStmt->close();
        }
        $checkStmt->close();
        exit;
    }

    if ($action === 'delete_comment') {
        $comment_id = $_POST['comment_id'];
        $is_admin = isAdmin($conn);
        
        // Admin can delete any comment, regular users can only delete their own
        $deleteSql = $is_admin 
            ? "DELETE FROM comments WHERE id = ?"
            : "DELETE FROM comments WHERE id = ? AND user_id = ?";
        
        $deleteStmt = $conn->prepare($deleteSql);
        
        if ($is_admin) {
            $deleteStmt->bind_param("i", $comment_id);
        } else {
            $deleteStmt->bind_param("ii", $comment_id, $user_id);
        }
    
        if ($deleteStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comment deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting comment: ' . $deleteStmt->error]);
        }
        $deleteStmt->close();
        exit;
    }

    if ($action === 'edit_comment') {
        $comment_id = $_POST['comment_id'];
        $new_content = htmlspecialchars($_POST['comment']);
        $is_admin = isAdmin($conn);
        
        // Admin can edit any comment, regular users can only edit their own
        $updateSql = $is_admin
            ? "UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ?"
            : "UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        
        if ($is_admin) {
            $updateStmt->bind_param("si", $new_content, $comment_id);
        } else {
            $updateStmt->bind_param("sii", $new_content, $comment_id, $user_id);
        }
    
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Comment updated successfully!',
                'new_content' => $new_content
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating comment: ' . $updateStmt->error]);
        }
        $updateStmt->close();
        exit;
    }

    if ($action === 'delete_discussion') {
        $discussion_id = $_POST['discussion_id'];

        $is_admin = isAdmin($conn);
        $delete_sql = $is_admin
            ? "SELECT user_id FROM discussions WHERE id = ?"
            : "SELECT user_id FROM discussions WHERE id = ? AND user_id = ?";

        $delete_stmt = $conn->prepare($delete_sql);
        if ($is_admin) {
            $delete_stmt->bind_param("i", $discussion_id);
        } else {
            $delete_stmt->bind_param("ii", $discussion_id, $user_id);
        }
        $delete_stmt->execute();
        $result = $delete_stmt->get_result();
        $discussion = $result->fetch_assoc();
        $delete_stmt->close();

        if ($discussion) {
            $delete_comments_sql = "DELETE FROM comments WHERE discussion_id = ?";
            $delete_comments_stmt = $conn->prepare($delete_comments_sql);
            $delete_comments_stmt->bind_param("i", $discussion_id);
            $delete_comments_stmt->execute();
            $delete_comments_stmt->close();

            $delete_discussion_sql = "DELETE FROM discussions WHERE id = ?";
            $delete_discussion_stmt = $conn->prepare($delete_discussion_sql);
            $delete_discussion_stmt->bind_param("i", $discussion_id);

            if ($delete_discussion_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Discussion deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting discussion: ' . $delete_discussion_stmt->error]);
            }
            $delete_discussion_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this discussion.']);
        }
        exit;
    }

    if ($action === 'react_comment') {
        $comment_id = $_POST['comment_id'];
    
        // Check if the user already reacted
        $checkSql = "SELECT * FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $comment_id, $user_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
    
        if ($result->num_rows > 0) {
            // Remove reaction
            $deleteSql = "DELETE FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("ii", $comment_id, $user_id);
            $deleteStmt->execute();
            $deleteStmt->close();
            $message = "Reaction removed successfully!";
        } else {
            // Add reaction
            $insertSql = "INSERT INTO comment_reactions (comment_id, user_id) VALUES (?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ii", $comment_id, $user_id);
            $insertStmt->execute();
            $insertStmt->close();
            $message = "Reaction added successfully!";
        }
        $checkStmt->close();
    
        // Get the updated reaction count
        $reactionCountSql = "SELECT COUNT(*) AS reaction_count FROM comment_reactions WHERE comment_id = ?";
        $reactionCountStmt = $conn->prepare($reactionCountSql);
        $reactionCountStmt->bind_param("i", $comment_id);
        $reactionCountStmt->execute();
        $reactionCountResult = $reactionCountStmt->get_result();
        $reactionCount = $reactionCountResult->fetch_assoc()['reaction_count'];
        $reactionCountStmt->close();
    
        echo json_encode(['success' => true, 'message' => $message, 'reaction_count' => $reactionCount]);
        exit;
    }

    if ($action === 'share_recipe') {
        $recipe_id = intval($_POST['recipe_id']);
        $message = htmlspecialchars($_POST['recipe_message'] ?? '');
        $result = shareRecipe($conn, $recipe_id, $user_id, $message);
        echo json_encode($result);
        exit;
    }

    if ($action === 'edit_discussion') {
        $discussion_id = $_POST['discussion_id'];
        $new_title = htmlspecialchars($_POST['title']);
        $new_content = htmlspecialchars($_POST['content']);

        $is_admin = isAdmin($conn);
        $check_sql = $is_admin
            ? "SELECT user_id, recipe_id, title FROM discussions WHERE id = ?"
            : "SELECT user_id, recipe_id, title FROM discussions WHERE id = ? AND user_id = ?";

        $check_stmt = $conn->prepare($check_sql);
        if ($is_admin) {
            $check_stmt->bind_param("i", $discussion_id);
        } else {
            $check_stmt->bind_param("ii", $discussion_id, $user_id);
        }
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $discussion = $check_result->fetch_assoc();
        $check_stmt->close();

        if ($discussion) {
            if (!empty($discussion['recipe_id'])) {
                $recipe_sql = "SELECT name FROM recipes WHERE id = ?";
                $recipe_stmt = $conn->prepare($recipe_sql);
                $recipe_stmt->bind_param("i", $discussion['recipe_id']);
                $recipe_stmt->execute();
                $recipe_result = $recipe_stmt->get_result();
                $recipe = $recipe_result->fetch_assoc();
                $recipe_stmt->close();

                $new_title = "Shared Recipe: " . htmlspecialchars($recipe['name']);
            }

            $update_sql = "UPDATE discussions SET title = ?, content = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $new_title, $new_content, $discussion_id);

            if ($update_stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Discussion updated successfully!',
                    'new_title' => $new_title,
                    'new_content' => $new_content
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating discussion: ' . $update_stmt->error]);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to edit this discussion.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Engagement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="community.css">
</head>

<body>
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
                            <a class="nav-link" href="view_meal_plans.php">View Meal Plan</a>
                        </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="community.php">Community</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="competitions.php">Competitions</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo getCurrentUsername(); ?></span>
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

    <div class="container mt-4">
        <h1 class="mb-4 text-center" style="color: #343a40;">Community Discussions</h1>

        <!-- Create New Discussion -->
        <div class="create-discussion mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 style="color: #343a40;">Start a New Discussion</h4>
                <?php if (isLoggedIn()): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#shareRecipeModal">
                        <i class="fas fa-share-alt me-2"></i>Share a Recipe
                    </button>
                <?php endif; ?>
            </div>

            <?php if (isLoggedIn()): ?>
                <form id="create-discussion-form">
                    <input type="hidden" name="action" value="create_discussion">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="title" name="title" placeholder="Discussion Title"
                            required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" id="content" name="content" rows="4"
                            placeholder="Share your thoughts..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Create Discussion
                    </button>
                </form>
            <?php else: ?>
                <p class="login-required"><i class="fas fa-lock me-2"></i>Please <a href="login.php">log in</a> to start a
                    new discussion.</p>
            <?php endif; ?>
        </div>

        <!-- Filter Section -->
        <div class="filter-container">
            <label for="sort-filter" class="fw-bold">Sort by:</label>
            <select id="sort-filter" class="form-select filter-select" onchange="applyFilter(this.value)">
                <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
            </select>
        </div>

        <!-- Display Discussions and Comments -->
        <h2 class="mb-4" style="color: #343a40;">Recent Discussions</h2>
        <div id="discussions-list">
            <?php
            $sql = "SELECT d.*, u.username, 
                    (SELECT AVG(rating) FROM ratings WHERE discussion_id = d.id) as avg_rating,
                    (SELECT COUNT(*) FROM comments WHERE discussion_id = d.id) as comment_count
                    FROM discussions d
                    JOIN users u ON d.user_id = u.id
                    ORDER BY $order_by";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $discussion_id = $row['id'];
                    $avg_rating = $row['avg_rating'] ? round($row['avg_rating'], 1) : "Not rated";
                    $is_recipe = !empty($row['recipe_id']);
                    ?>
                    <div class="card discussion-card <?php echo $is_recipe ? 'recipe-card' : ''; ?>"
                        id="discussion-<?php echo $discussion_id; ?>">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-0">
                                    <?php if ($is_recipe): ?>
                                        <i class="fas fa-utensils me-2"></i>
                                        <span class="discussion-title"><?php echo $row['title']; ?></span>
                                    <?php else: ?>
                                        <span class="discussion-title"><?php echo $row['title']; ?></span>
                                    <?php endif; ?>
                                </h5>
                                <div class="rating">
                                    <i class="fas fa-star me-1"></i><span class="avg-rating"><?php echo $avg_rating; ?></span>/5
                                </div>
                            </div>
                            <div class="discussion-meta mt-2">
                                <i class="fas fa-user me-1"></i><?php echo $row['username']; ?>
                                <span class="mx-2">•</span>
                                <i
                                    class="fas fa-clock me-1"></i><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?>
                            </div>
                            <?php if ($row['user_id'] == getCurrentUserId() || isAdmin($conn)): ?>
                                <div class="discussion-actions mt-2">
                                    <form class="d-inline">
                                        <input type="hidden" name="discussion_id" value="<?php echo $row['id']; ?>">
                                        <button type="button" class="btn btn-sm btn-primary me-2"
                                            onclick="editDiscussion(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['content'], ENT_QUOTES); ?>', <?php echo $is_recipe ? 'true' : 'false'; ?>)">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                    </form>
                                    <form class="delete-discussion-form d-inline">
                                        <input type="hidden" name="action" value="delete_discussion">
                                        <input type="hidden" name="discussion_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash me-1"></i> Delete
                                            <?php echo $is_recipe ? 'Recipe Share' : 'Discussion'; ?>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p class="mb-4 discussion-content"><?php echo $row['content']; ?></p>
                            <?php if ($is_recipe): ?>
                                <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="btn btn-sm btn-info mb-3">
                                    <i class="fas fa-eye me-1"></i>View Full Recipe
                                </a>
                            <?php endif; ?>

                            <!-- Rating Form -->
                            <?php if (isLoggedIn()): ?>
                                <form class="add-rating-form mb-4">
                                    <input type="hidden" name="action" value="add_rating">
                                    <input type="hidden" name="discussion_id" value="<?php echo $discussion_id; ?>">
                                    <div class="d-flex align-items-center gap-3">
                                        <select class="form-control w-auto" name="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-star me-1"></i>Rate
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <h6 class="mb-3"><i class="fas fa-comments me-2"></i><span
                                    class="comment-count"><?php echo $row['comment_count']; ?></span> Comments</h6>

                            <div class="comments-list">
                                <?php
                                $commentSql = "SELECT c.*, u.username 
                                       FROM comments c
                                       JOIN users u ON c.user_id = u.id
                                       WHERE c.discussion_id = ?
                                       ORDER BY c.created_at ASC";

                                $commentStmt = $conn->prepare($commentSql);
                                $commentStmt->bind_param("i", $discussion_id);
                                $commentStmt->execute();
                                $commentResult = $commentStmt->get_result();

                                if ($commentResult->num_rows > 0) {
                                    while ($comment = $commentResult->fetch_assoc()) {
                                        $reactionCountSql = "SELECT COUNT(*) AS reaction_count FROM comment_reactions WHERE comment_id = ?";
                                        $reactionCountStmt = $conn->prepare($reactionCountSql);
                                        $reactionCountStmt->bind_param("i", $comment['id']);
                                        $reactionCountStmt->execute();
                                        $reactionCountResult = $reactionCountStmt->get_result();
                                        $reactionCount = $reactionCountResult->fetch_assoc()['reaction_count'];
                                        $reactionCountStmt->close();
                                        ?>
                                        <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                                            <p class="mb-2 comment-content"><?php echo $comment['content']; ?></p>
                                            <small class="discussion-meta">
                                                <i class="fas fa-user me-1"></i><?php echo $comment['username']; ?>
                                                <?php 
                                                // Check if comment author is admin
                                                $author_is_admin = false;
                                                $check_admin_sql = "SELECT role FROM users WHERE id = ?";
                                                $check_admin_stmt = $conn->prepare($check_admin_sql);
                                                $check_admin_stmt->bind_param("i", $comment['user_id']);
                                                $check_admin_stmt->execute();
                                                $admin_result = $check_admin_stmt->get_result();
                                                if ($admin_result->num_rows > 0) {
                                                    $author = $admin_result->fetch_assoc();
                                                    $author_is_admin = $author['role'] === 'admin';
                                                }
                                                $check_admin_stmt->close();
                                                
                                                if ($author_is_admin): ?>
                                                    <span class="badge bg-danger ms-2">Admin</span>
                                                <?php endif; ?>
                                                <span class="mx-2">•</span>
                                                <?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?>
                                            </small>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <div class="reaction">
                                                    <form class="d-inline"
                                                        onsubmit="return reactToComment(event, <?php echo $comment['id']; ?>)">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                        <button type="submit" name="react_comment"
                                                            class="btn btn-sm btn-link text-primary">
                                                            <i class="fas fa-thumbs-up"></i> Like (<span
                                                                id="reaction-count-<?php echo $comment['id']; ?>"><?php echo $reactionCount; ?></span>)
                                                        </button>
                                                    </form>
                                                </div>
                                                <?php if ($comment['user_id'] == getCurrentUserId() || isAdmin($conn)): ?>
                                                    <div class="comment-actions">
                                                        <form class="d-inline">
                                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                            <button type="button" class="btn btn-sm btn-link text-primary"
                                                                onclick="editComment(<?php echo $comment['id']; ?>, '<?php echo htmlspecialchars($comment['content'], ENT_QUOTES); ?>')">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                        </form>
                                                        <form class="delete-comment-form d-inline">
                                                            <input type="hidden" name="action" value="delete_comment">
                                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-link text-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo "<p class='no-comments'>No comments yet.</p>";
                                }
                                $commentStmt->close();
                                ?>
                            </div>

                            <!-- Add Comment Form -->
                            <?php if (isLoggedIn()): ?>
                                <form class="add-comment-form mt-4">
                                    <input type="hidden" name="action" value="add_comment">
                                    <input type="hidden" name="discussion_id" value="<?php echo $discussion_id; ?>">
                                    <div class="input-group">
                                        <textarea class="form-control" name="comment" rows="2" placeholder="Add your comment..."
                                            required></textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="login-required mt-3"><i class="fas fa-lock me-2"></i>Please <a href="login.php">log in</a>
                                    to comment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No discussions yet. Be the first to start a discussion!</p>";
            }
            ?>
        </div>
    </div>

    <!-- Share Recipe Modal -->
    <div class="modal fade" id="shareRecipeModal" tabindex="-1" aria-labelledby="shareRecipeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="share-recipe-form">
                    <input type="hidden" name="action" value="share_recipe">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareRecipeModalLabel">Share Your Recipe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="recipe_id" class="form-label">Select a Recipe</label>
                            <select class="form-select" id="recipe_id" name="recipe_id" required>
                                <option value="">-- Choose a Recipe --</option>
                                <?php
                                $user_id = getCurrentUserId();
                                $recipe_sql = "SELECT id, name FROM recipes WHERE user_id = ?";
                                $recipe_stmt = $conn->prepare($recipe_sql);
                                $recipe_stmt->bind_param("i", $user_id);
                                $recipe_stmt->execute();
                                $recipe_result = $recipe_stmt->get_result();

                                while ($recipe = $recipe_result->fetch_assoc()) {
                                    echo "<option value='{$recipe['id']}'>" . htmlspecialchars($recipe['name']) . "</option>";
                                }
                                $recipe_stmt->close();
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="recipe_message" class="form-label">Add a Message (Optional)</label>
                            <textarea class="form-control" id="recipe_message" name="recipe_message" rows="3"
                                placeholder="Tell the community about this recipe..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Share Recipe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Comment Modal -->
    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-comment-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_comment">
                        <input type="hidden" name="comment_id" id="edit-comment-id">
                        <textarea class="form-control" name="comment" id="edit-comment-content" rows="3"
                            required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Discussion Modal -->
    <div class="modal fade" id="editDiscussionModal" tabindex="-1" aria-labelledby="editDiscussionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-discussion-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDiscussionModalLabel">Edit Discussion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_discussion">
                        <input type="hidden" name="discussion_id" id="edit-discussion-id">
                        <div class="mb-3">
                            <label for="edit-discussion-title" class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="edit-discussion-title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-discussion-content" class="form-label">Content</label>
                            <textarea class="form-control" name="content" id="edit-discussion-content" rows="4"
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilter(sortValue) {
            window.location.href = '?sort=' + sortValue;
        }

        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} fixed-top mx-auto mt-3 w-50 text-center`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        function editComment(commentId, currentContent) {
            const commentTextarea = document.getElementById('edit-comment-content');
            const commentIdInput = document.getElementById('edit-comment-id');
            commentTextarea.value = currentContent;
            commentIdInput.value = commentId;
            const editModal = new bootstrap.Modal(document.getElementById('editCommentModal'));
            editModal.show();
        }

        function editDiscussion(discussionId, currentTitle, currentContent, isRecipe) {
            const titleInput = document.getElementById('edit-discussion-title');
            const contentTextarea = document.getElementById('edit-discussion-content');
            const discussionIdInput = document.getElementById('edit-discussion-id');

            titleInput.value = currentTitle;
            contentTextarea.value = currentContent;
            discussionIdInput.value = discussionId;

            if (isRecipe) {
                titleInput.setAttribute('readonly', 'readonly');
            } else {
                titleInput.removeAttribute('readonly');
            }

            const editModal = new bootstrap.Modal(document.getElementById('editDiscussionModal'));
            editModal.show();
        }

        function reactToComment(event, commentId) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('action', 'react_comment');
            formData.append('comment_id', commentId);

            fetch('community.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const reactionCountElement = document.getElementById(`reaction-count-${commentId}`);
                        reactionCountElement.textContent = data.reaction_count;
                        showNotification('success', data.message);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                });

            return false;
        }

        // Handle Create Discussion Form
        document.getElementById('create-discussion-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('community.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        const discussionsList = document.getElementById('discussions-list');
                        const newDiscussion = document.createElement('div');
                        newDiscussion.className = 'card discussion-card';
                        newDiscussion.id = `discussion-${data.new_discussion_id}`;
                        newDiscussion.innerHTML = `
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h5 class="mb-0">
                                        <span class="discussion-title">${data.title}</span>
                                    </h5>
                                    <div class="rating">
                                        <i class="fas fa-star me-1"></i><span class="avg-rating">Not rated</span>/5
                                    </div>
                                </div>
                                <div class="discussion-meta mt-2">
                                    <i class="fas fa-user me-1"></i>${data.username}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>${data.created_at}
                                </div>
                                <div class="discussion-actions mt-2">
                                    <form class="d-inline">
                                        <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                        <button type="button" class="btn btn-sm btn-primary me-2"
                                            onclick="editDiscussion(${data.new_discussion_id}, '${data.title}', '${data.content}', false)">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                    </form>
                                    <form class="delete-discussion-form d-inline">
                                        <input type="hidden" name="action" value="delete_discussion">
                                        <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash me-1"></i> Delete Discussion
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-4 discussion-content">${data.content}</p>
                                <form class="add-rating-form mb-4">
                                    <input type="hidden" name="action" value="add_rating">
                                    <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                    <div class="d-flex align-items-center gap-3">
                                        <select class="form-control w-auto" name="rating">
                                            <option value="1">1 Star</option>
                                            <option value="2">2 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="5">5 Stars</option>
                                        </select>
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-star me-1"></i>Rate
                                        </button>
                                    </div>
                                </form>
                                <h6 class="mb-3"><i class="fas fa-comments me-2"></i><span class="comment-count">0</span> Comments</h6>
                                <div class="comments-list">
                                    <p class="no-comments">No comments yet.</p>
                                </div>
                                <form class="add-comment-form mt-4">
                                    <input type="hidden" name="action" value="add_comment">
                                    <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                    <div class="input-group">
                                        <textarea class="form-control" name="comment" rows="2" placeholder="Add your comment..." required></textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        `;
                        discussionsList.prepend(newDiscussion);
                        this.reset();
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                });
        });

        // Handle Add Comment Forms
        document.querySelectorAll('.add-comment-form').forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                const discussionId = formData.get('discussion_id');

                fetch('community.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', data.message);
                            const commentsList = document.querySelector(`#discussion-${discussionId} .comments-list`);
                            const noComments = commentsList.querySelector('.no-comments');
                            if (noComments) {
                                noComments.remove();
                            }
                            const newComment = document.createElement('div');
                            newComment.className = 'comment';
                            newComment.id = `comment-${data.new_comment_id}`;
                            newComment.innerHTML = `
                                <p class="mb-2 comment-content">${data.content}</p>
                                <small class="discussion-meta">
                                    <i class="fas fa-user me-1"></i>${data.username}
                                    <span class="mx-2">•</span>
                                    ${data.created_at}
                                </small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="reaction">
                                        <form class="d-inline" onsubmit="return reactToComment(event, ${data.new_comment_id})">
                                            <input type="hidden" name="comment_id" value="${data.new_comment_id}">
                                            <button type="submit" name="react_comment" class="btn btn-sm btn-link text-primary">
                                                <i class="fas fa-thumbs-up"></i> Like (<span id="reaction-count-${data.new_comment_id}">0</span>)
                                            </button>
                                        </form>
                                    </div>
                                    <div class="comment-actions">
                                        <form class="d-inline">
                                            <input type="hidden" name="comment_id" value="${data.new_comment_id}">
                                            <button type="button" class="btn btn-sm btn-link text-primary"
                                                onclick="editComment(${data.new_comment_id}, '${data.content}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </form>
                                        <form class="delete-comment-form d-inline">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="${data.new_comment_id}">
                                            <button type="submit" class="btn btn-sm btn-link text-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            `;
                            commentsList.appendChild(newComment);
                            const commentCount = document.querySelector(`#discussion-${discussionId} .comment-count`);
                            commentCount.textContent = parseInt(commentCount.textContent) + 1;
                            form.reset();
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'An error occurred. Please try again.');
                    });
            });
        });

        // Handle Add Rating Forms
        document.querySelectorAll('.add-rating-form').forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                const discussionId = formData.get('discussion_id');

                fetch('community.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', data.message);
                            const avgRating = document.querySelector(`#discussion-${discussionId} .avg-rating`);
                            avgRating.textContent = data.avg_rating === 0 ? 'Not rated' : data.avg_rating;
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'An error occurred. Please try again.');
                    });
            });
        });

        // Handle Delete Comment Forms
        document.querySelectorAll('.delete-comment-form').forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                const commentId = formData.get('comment_id');
                const discussionId = document.querySelector(`#comment-${commentId}`).closest('.discussion-card').id.split('-')[1];

                fetch('community.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', data.message);
                            const comment = document.getElementById(`comment-${commentId}`);
                            comment.remove();
                            const commentCount = document.querySelector(`#discussion-${discussionId} .comment-count`);
                            const newCount = parseInt(commentCount.textContent) - 1;
                            commentCount.textContent = newCount;
                            if (newCount === 0) {
                                const commentsList = document.querySelector(`#discussion-${discussionId} .comments-list`);
                                commentsList.innerHTML = '<p class="no-comments">No comments yet.</p>';
                            }
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'An error occurred. Please try again.');
                    });
            });
        });

        // Handle Edit Comment Form
        document.getElementById('edit-comment-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);
            const commentId = formData.get('comment_id');

            fetch('community.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        const commentContent = document.querySelector(`#comment-${commentId} .comment-content`);
                        commentContent.textContent = data.new_content;
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editCommentModal'));
                        modal.hide();
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                });
        });

        // Handle Delete Discussion Forms
        document.querySelectorAll('.delete-discussion-form').forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const formData = new FormData(this);
                const discussionId = formData.get('discussion_id');

                fetch('community.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', data.message);
                            const discussion = document.getElementById(`discussion-${discussionId}`);
                            discussion.remove();
                            const discussionsList = document.getElementById('discussions-list');
                            if (!discussionsList.children.length) {
                                discussionsList.innerHTML = '<p>No discussions yet. Be the first to start a discussion!</p>';
                            }
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'An error occurred. Please try again.');
                    });
            });
        });

        // Handle Share Recipe Form
        document.getElementById('share-recipe-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('community.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        const discussionsList = document.getElementById('discussions-list');
                        const newDiscussion = document.createElement('div');
                        newDiscussion.className = `card discussion-card recipe-card`;
                        newDiscussion.id = `discussion-${data.new_discussion_id}`;
                        newDiscussion.innerHTML = `
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h5 class="mb-0">
                                        <i class="fas fa-utensils me-2"></i>
                                        <span class="discussion-title">${data.title}</span>
                                    </h5>
                                    <div class="rating">
                                        <i class="fas fa-star me-1"></i><span class="avg-rating">Not rated</span>/5
                                    </div>
                                </div>
                                <div class="discussion-meta mt-2">
                                    <i class="fas fa-user me-1"></i>${data.username}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>${data.created_at}
                                </div>
                                <div class="discussion-actions mt-2">
                                    <form class="d-inline">
                                        <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                        <button type="button" class="btn btn-sm btn-primary me-2"
                                            onclick="editDiscussion(${data.new_discussion_id}, '${data.title}', '${data.content}', true)">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                    </form>
                                    <form class="delete-discussion-form d-inline">
                                        <input type="hidden" name="action" value="delete_discussion">
                                        <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash me-1"></i> Delete Recipe Share
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-4 discussion-content">${data.content}</p>
                                <a href="recipe_view.php?id=${data.recipe_id}" class="btn btn-sm btn-info mb-3">
                                    <i class="fas fa-eye me-1"></i>View Full Recipe
                                </a>
                                <form class="add-rating-form mb-4">
                                    <input type="hidden" name="action" value="add_rating">
                                    <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                    <div class="d-flex align-items-center gap-3">
                                        <select class="form-control w-auto" name="rating">
                                            <option value="1">1 Star</option>
                                            <option value="2">2 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="5">5 Stars</option>
                                        </select>
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-star me-1"></i>Rate
                                        </button>
                                    </div>
                                </form>
                                <h6 class="mb-3"><i class="fas fa-comments me-2"></i><span class="comment-count">0</span> Comments</h6>
                                <div class="comments-list">
                                    <p class="no-comments">No comments yet.</p>
                                </div>
                                <form class="add-comment-form mt-4">
                                    <input type="hidden" name="action" value="add_comment">
                                    <input type="hidden" name="discussion_id" value="${data.new_discussion_id}">
                                    <div class="input-group">
                                        <textarea class="form-control" name="comment" rows="2" placeholder="Add your comment..." required></textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        `;
                        discussionsList.prepend(newDiscussion);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('shareRecipeModal'));
                        modal.hide();
                        this.reset();
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                });
        });

        // Handle Edit Discussion Form
        document.getElementById('edit-discussion-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);
            const discussionId = formData.get('discussion_id');

            fetch('community.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        const discussionTitle = document.querySelector(`#discussion-${discussionId} .discussion-title`);
                        const discussionContent = document.querySelector(`#discussion-${discussionId} .discussion-content`);
                        discussionTitle.textContent = data.new_title;
                        discussionContent.textContent = data.new_content;
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editDiscussionModal'));
                        modal.hide();
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'An error occurred. Please try again.');
                });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
<?php
// Include the header
require_once 'header.php';

// Get the recipe ID from the query parameter
$recipe_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($recipe_id <= 0) {
    die("Invalid recipe ID.");
}

// Fetch the recipe details including image path
$recipe_sql = "SELECT r.id, r.name, r.cuisine, r.image, r.user_id, u.username 
               FROM recipes r 
               JOIN users u ON r.user_id = u.id 
               WHERE r.id = ?";
$recipe_stmt = $conn->prepare($recipe_sql);
$recipe_stmt->bind_param("i", $recipe_id);
$recipe_stmt->execute();
$recipe_result = $recipe_stmt->get_result();

if ($recipe_result->num_rows === 0) {
    die("Recipe not found.");
}

$recipe = $recipe_result->fetch_assoc();
$recipe_stmt->close();

// Fetch the ingredients for this recipe
$ingredients_sql = "SELECT name, quantity 
                    FROM ingredients 
                    WHERE recipe_id = ? 
                    ORDER BY id";
$ingredients_stmt = $conn->prepare($ingredients_sql);
$ingredients_stmt->bind_param("i", $recipe_id);
$ingredients_stmt->execute();
$ingredients_result = $ingredients_stmt->get_result();
?>

<style>
    .recipe-container {
        padding: 40px 0 80px 0;
    }

    .recipe-header {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        text-align: center;
    }

    .recipe-image {
        max-width: 100%;
        max-height: 500px;
        object-fit: contain;
        border-radius: 8px;
        margin-bottom: 20px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .recipe-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 10px;
    }

    .recipe-meta {
        font-size: 1.1rem;
        color: #6c757d;
    }

    .section-card {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .ingredients-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .ingredients-list li {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
        font-size: 1rem;
        display: flex;
        justify-content: space-between;
    }

    .ingredients-list li:last-child {
        border-bottom: none;
    }

    .instructions {
        line-height: 1.8;
        color: #495057;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 500;
        color: #ffffff;
        background-color: #343a40;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background-color: #495057;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .back-btn i {
        margin-right: 8px;
    }

    .no-image {
        background-color: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        color: #6c757d;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .recipe-title {
            font-size: 2rem;
        }

        .recipe-image {
            max-height: 300px;
        }

        .recipe-container {
            padding: 20px 0 60px 0;
        }

        .recipe-header {
            padding: 20px;
        }

        .section-card {
            padding: 20px;
        }
    }
</style>

<!-- Recipe Details -->
<div class="content">
    <div class="recipe-container">
        <div class="container">
            <div class="recipe-header">
                <?php if (!empty($recipe['image'])): ?>
                    <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                        alt="<?php echo htmlspecialchars($recipe['name']); ?>" class="recipe-image">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-camera fa-3x mb-3"></i>
                        <p>No image available for this recipe</p>
                    </div>
                <?php endif; ?>

                <h1 class="recipe-title"><?php echo htmlspecialchars($recipe['name']); ?></h1>
                <p class="recipe-meta">Cuisine: <strong><?php echo htmlspecialchars($recipe['cuisine']); ?></strong></p>
                <p class="recipe-meta">Posted by: <strong><?php echo htmlspecialchars($recipe['username']); ?></strong>
                </p>
            </div>

            <!-- Ingredients List -->
            <div class="section-card ingredients-list">
                <h3 class="mb-4">Ingredients</h3>
                <?php if ($ingredients_result->num_rows > 0): ?>
                    <ul>
                        <?php while ($ingredient = $ingredients_result->fetch_assoc()): ?>
                            <li>
                                <span><?php echo htmlspecialchars($ingredient['name']); ?></span>
                                <span><?php echo htmlspecialchars($ingredient['quantity']) ?: 'As needed'; ?></span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No ingredients found for this recipe.</p>
                <?php endif; ?>
            </div>

            <!-- Back to Community Button -->
            <div class="text-center mt-4">
                <a href="community.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Community
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$ingredients_stmt->close();
$conn->close();

// Include the footer
require_once 'footer.php';
?>
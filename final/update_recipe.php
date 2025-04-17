<?php
include 'db.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("Recipe not found.");
}

$recipe_id = $_GET['id'];

// Fetch the recipe
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe || $recipe['user_id'] != $_SESSION['user_id']) {
    die("Unauthorized or invalid recipe.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $cuisine = $_POST['cuisine'];

    // Image logic
    $image_path = $recipe['image'];
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'uploads/';
        $image_name = basename($_FILES['image']['name']);
        $target_path = $upload_dir . time() . "_" . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            if (!empty($recipe['image']) && file_exists($recipe['image'])) {
                unlink($recipe['image']);
            }
            $image_path = $target_path;
        }
    }

    // Update recipe
    $stmt = $conn->prepare("UPDATE recipes SET name = ?, cuisine = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $cuisine, $image_path, $recipe_id);
    $stmt->execute();

    // Update ingredients: remove all, then re-add
    $conn->query("DELETE FROM ingredients WHERE recipe_id = $recipe_id");
    foreach ($_POST['ingredient_name'] as $i => $name) {
        $qty = $_POST['ingredient_qty'][$i];
        $stmt = $conn->prepare("INSERT INTO ingredients (recipe_id, name, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $recipe_id, $name, $qty);
        $stmt->execute();
    }

    // Update steps: remove all, then re-add
    $conn->query("DELETE FROM steps WHERE recipe_id = $recipe_id");
    foreach ($_POST['steps'] as $i => $desc) {
        $step_number = $i + 1;
        $stmt = $conn->prepare("INSERT INTO steps (recipe_id, step_number, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $recipe_id, $step_number, $desc);
        $stmt->execute();
    }

    header("Location: view_recipe.php?id=$recipe_id&updated=true");
    exit;
}

// Fetch ingredients and steps
$ingredients = $conn->query("SELECT name, quantity FROM ingredients WHERE recipe_id = $recipe_id");
$steps = $conn->query("SELECT description FROM steps WHERE recipe_id = $recipe_id ORDER BY step_number");



?>

<link rel="stylesheet" href="style_recipe.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="recipe-body">
<div class="recipe-container">
    <h2 style="text-align:center;">Update Recipe</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Recipe Title:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Cuisine:</label>
            <select name="cuisine" required>
                <?php
                $cuisines = ['Italian', 'Chinese', 'Indian', 'Mexican', 'Malay', 'Others'];
                foreach ($cuisines as $cuisine) {
                    $selected = ($recipe['cuisine'] === $cuisine) ? 'selected' : '';
                    echo "<option value=\"$cuisine\" $selected>$cuisine</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Update Recipe Image:</label>
            <input type="file" name="image" accept="image/*">
            <?php if (!empty($recipe['image'])): ?>
                <img src="<?= $recipe['image'] ?>" style="max-width: 100%; margin-top: 10px; border-radius: 10px;">
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Ingredients:</label>
            <div id="ingredients">
                <?php while ($row = $ingredients->fetch_assoc()): ?>
                    <div class="ingredient-pair">
                        <input type="text" name="ingredient_name[]" value="<?= htmlspecialchars($row['name']) ?>" required>
                        <input type="text" name="ingredient_qty[]" value="<?= htmlspecialchars($row['quantity']) ?>" required>
                        <button type="button" onclick="this.parentElement.remove()" 
                            style="padding:10px 20px; background-color:#f44336; color:white; border:none; border-radius:30px; cursor:pointer;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="form-buttons">
                <button type="button" onclick="addIngredient()">+ Add Ingredient</button>
            </div>
        </div>

        <div class="form-group">
            <label>Preparation Steps:</label>
            <div id="steps">
                <?php 
                $stepNum = 1;
                while ($row = $steps->fetch_assoc()): ?>
                    <div class="ingredient-pair">
                        <textarea name="steps[]" required class="step-textarea" style="resize:none;"><?= htmlspecialchars($row['description']) ?></textarea>
                        <button type="button" onclick="this.parentElement.remove()" 
                            style="padding:10px 20px; background-color:#f44336; color:white; border:none; border-radius:30px; cursor:pointer;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                <?php $stepNum++; endwhile; ?>
            </div>
            <div class="form-buttons">
                <button type="button" onclick="addStep()">+ Add Step</button>
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" style="background-color:#007bff; color:white; border-radius:30px; padding:10px 20px;">Update Recipe</button>
        </div>
    </form>
</div>
</div>
<script>
function addIngredient() {
    const div = document.createElement('div');
    div.className = 'ingredient-pair';
    div.innerHTML = `
        <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
        <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
        <button type="button" onclick="this.parentElement.remove()" 
            style="padding:10px 20px; background-color:#f44336; color:white; border:none; border-radius:30px; cursor:pointer;">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;
    document.getElementById('ingredients').appendChild(div);
}

function addStep() {
    const stepCount = document.querySelectorAll('#steps textarea').length + 1;
    const div = document.createElement('div');
    div.className = 'ingredient-pair';
    div.innerHTML = `
        <textarea name="steps[]" placeholder="Step ${stepCount}" required class="step-textarea" style="resize: none;"></textarea>
        <button type="button" onclick="this.parentElement.remove()" 
            style="padding:10px 20px; background-color:#f44336; color:white; border:none; border-radius:30px; cursor:pointer;">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;
    document.getElementById('steps').appendChild(div);
}

function updateStepPlaceholders() {
    const steps = document.querySelectorAll('#steps textarea');
    steps.forEach((el, i) => el.placeholder = `Step ${i + 1}`);
}
</script>
<script>
// Confirmation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent immediate submit
    
    Swal.fire({
        title: 'Update Recipe?',
        text: "Are you sure you want to update this recipe?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Update!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading indicator
            Swal.showLoading();
            
            // Programmatically submit the form
            this.submit();
        }
    });
});


</script>
<?php include 'footer.php'; ?>



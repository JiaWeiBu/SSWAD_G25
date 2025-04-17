<?php 
include 'db.php'; 
include 'header.php'; 
include 'auth.php';

if (!isset($_GET['id'])) {
    echo "<p style='text-align:center;color:red;'>Recipe not found.</p>";
    exit;
}

$recipe_id = $_GET['id'];

$stmt = $conn->prepare("SELECT name, cuisine, image FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='text-align:center;color:red;'>Recipe not found.</p>";
    exit;
}

$recipe = $result->fetch_assoc();
?>

<link rel="stylesheet" href="style_recipe.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="recipe-body">
    <div class="recipe-container">
        <a href="my_recipes.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h2 style="text-align:center"><?= htmlspecialchars($recipe['name']) ?></h2>
        <p style="text-align:center; font-style:italic;"><?= htmlspecialchars($recipe['cuisine']) ?></p>

        <?php if (!empty($recipe['image'])): ?>
            <div style="text-align:center;">
            <img src="<?= htmlspecialchars($recipe['image']) ?>" alt="Recipe Image"
                style="width:800px; height:500px; object-fit:cover; border-radius:10px; margin:15px auto; display:block;">
            </div>
        <?php endif; ?>

        <br>
        <h3>Ingredients:</h3>
        <ul>
        <?php
        $stmt = $conn->prepare("SELECT name, quantity FROM ingredients WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $ingredients = $stmt->get_result();
        while ($row = $ingredients->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['quantity']) . " - " . htmlspecialchars($row['name']) . "</li>";
        }
        ?>
        </ul>

        <br>
        <h3>Preparation Steps:</h3>
        <ol>
        <?php
        $stmt = $conn->prepare("SELECT step_number, description FROM steps WHERE recipe_id = ? ORDER BY step_number ASC");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $steps = $stmt->get_result();
        while ($row = $steps->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['description']) . "</li>";
        }
        ?>
        </ol>

        <br>
        <div class="form-buttons" style="text-align:center; margin-top: 20px;">
            <a href="update_recipe.php?id=<?= $recipe_id ?>" class="btn-update">Update</a>
            <a href="delete_recipe.php?id=<?= $recipe_id ?>" class="btn-delete">Delete</a>
        </div>

        <button id="backToTopBtn" title="Go to top">
            <i class="fas fa-arrow-up"></i>
        </button>

    </div>
</div>

<script>
  const backToTopBtn = document.getElementById("backToTopBtn");

  window.onscroll = () => {
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
      backToTopBtn.style.display = "block";
    } else {
      backToTopBtn.style.display = "none";
    }
  };

  backToTopBtn.onclick = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

</script>
<?php if (isset($_GET['updated'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Updated!',
        text: 'Recipe has been successfully updated!',
        showConfirmButton: false,
        timer: 2000
    });
});
</script>
<?php endif; ?>

<script>
document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const deleteUrl = this.href;
        
        Swal.fire({
            title: 'Delete Recipe?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading indicator
                Swal.showLoading();
                
                // Send AJAX request
                fetch(deleteUrl)
                    .then(response => {
                        if (response.ok) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Recipe deleted successfully!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Redirect to my_recipes.php after success
                                window.location.href = 'my_recipes.php';
                            });
                        } else {
                            Swal.fire('Error', 'Failed to delete recipe', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'An error occurred', 'error');
                    });
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
<?php 
include 'db.php'; 
include 'header.php'; 
include 'auth.php';
?>

<link rel="stylesheet" href="style_recipe.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="recipe-body">
    <div class="recipe-container">
        <h2 style="text-align:center">Create a New Recipe</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Recipe Title:</label>
                <input type="text" name="name" required>
            </div>
            <br>
            <div class="form-group">
                <label for="cuisine" class="col-form-label col-6">Cuisine:</label>
                <div class="row">    
                    <div class="col-5">
                        <select id="cuisine" name="cuisine" onchange="handleCuisineChange()" required>
                            <option value="">-- Select Cuisine --</option>
                            <option value="Malay">Malay</option>
                            <option value="Chinese">Chinese</option>
                            <option value="Indian">Indian</option>
                            <option value="Italian">Italian</option>
                            <option value="French">French</option>
                            <option value="Japanese">Japanese</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <!-- Hidden input for custom cuisine -->
                    <div class="col-5" id="customCuisineContainer" style="display: none;">
                        <input type="text" id="customCuisine" name="customCuisine" placeholder="Enter other cuisine" class="form-control">
                    </div>
                </div>
            </div>
            <br>
            <div class="">
                <label for="image">Add Recipe Image:</label>
                <label for="imageUpload" class="upload-btn">Choose Image</label>
                <input type="file" id="imageUpload" name="image" accept="image/*" onchange="previewImage(event)" style="display: none;">
                <img id="imagePreview" style="display:none; margin-top:10px; max-width:50%; border-radius:8px;" />
            </div>
            <br>
            <div class="form-group">
                <label>Ingredients:</label>
                <div id="ingredients" class="row">
                    <div class="col-5" style="margin-bottom: 5px; width:390px;">
                        <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required>
                    </div>
                    <div class="col-5" style="width:390px;">
                        <input type="text" name="ingredient_qty[]" placeholder="Quantity" required>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" onclick="addIngredient()">+ Add Ingredient</button>
                </div>
            </div>
            <br>
            <div class="form-group">
                <label>Preparation Steps:</label>
                <div id="steps" class="row">
                    <div class="col-10" style="margin-bottom: 5px; width:783px;" >
                        <textarea name="steps[]" placeholder="Step 1" required class="step-textarea" style="resize: none;"></textarea>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" onclick="addStep()">+ Add Step</button>
                </div>
            </div>
            <br>

            <div class="form-buttons">
                <button type="submit" >Create Recipe</button>
            </div>

            <button id="backToTopBtn" title="Go to top">
                <i class="fas fa-arrow-up"></i>
            </button>
        </form>
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

    function handleCuisineChange() {
        const cuisineSelect = document.getElementById("cuisine");
        const customInputContainer = document.getElementById("customCuisineContainer");

        if (cuisineSelect.value === "Others") {
            customInputContainer.style.display = "block";  // Show the input when "Others" is selected
        } else {
            customInputContainer.style.display = "none";  // Hide the input when not "Others"
            document.getElementById("customCuisine").value = "";  // Clear the input field
        }
    }
    
    function addIngredient() {
        const row = document.createElement('div');
        row.className = 'row';
        row.style.marginBottom = '5px';

        row.innerHTML = `
            <div class="col-5">
                <input type="text" name="ingredient_name[]" placeholder="Ingredient Name" required style="width: 100%;">
            </div>
            <div class="col-5">
                <input type="text" name="ingredient_qty[]" placeholder="Quantity" required style="width: 100%;">
            </div>
            <div class="col-1 d-flex align-items-center">
                <button type="button" onclick="this.closest('.row').remove()" 
                    style="padding:9px 19px; background-color:#f44336; color:white; border:none; border-radius:30px; cursor:pointer;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
        document.getElementById('ingredients').appendChild(row);
    }


    function addStep() {
        const stepCount = document.querySelectorAll('#steps textarea').length + 1;

        const row = document.createElement('div');
        row.className = 'row align-items-center'; // align center vertically and add margin bottom
        row.style.marginBottom = '5px';

        row.innerHTML = `
            <div class="col-10">
                <textarea name="steps[]" placeholder="Step ${stepCount}" required class="step-textarea" style="resize: none;"></textarea>
            </div>
            <div class="col-1 d-flex justify-content-center">
                <button type="button" onclick="this.closest('.row').remove(); updateStepPlaceholders()" 
                    style="padding: 9px 19px;background-color:#f44336; color:white; border:none; border-radius:30px; cursor:pointer;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;

        document.getElementById('steps').appendChild(row);
    }



    function updateStepPlaceholders() {
        const stepTextareas = document.querySelectorAll('#steps textarea');
        stepTextareas.forEach((el, idx) => {
            el.placeholder = 'Step ' + (idx + 1);
        });
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('imagePreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $cuisine = $_POST['cuisine'];
    $user_id = $_SESSION['user_id'];
    $image_path = null;

    if ($cuisine == 'Others' && !empty($_POST['customCuisine'])) {
        $cuisine = $_POST['customCuisine'];
    }

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'uploads/';
        $image_name = basename($_FILES['image']['name']);
        $target_path = $upload_dir . time() . "_" . $image_name;

        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }

    $stmt = $conn->prepare("INSERT INTO recipes (user_id, name, cuisine, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $name, $cuisine, $image_path);
    $stmt->execute();
    $recipe_id = $conn->insert_id;

    foreach ($_POST['ingredient_name'] as $index => $name) {
        $qty = $_POST['ingredient_qty'][$index];
        $stmt = $conn->prepare("INSERT INTO ingredients (recipe_id, name, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $recipe_id, $name, $qty);
        $stmt->execute();
    }

    foreach ($_POST['steps'] as $index => $desc) {
        $step_number = $index + 1;
        $stmt = $conn->prepare("INSERT INTO steps (recipe_id, step_number, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $recipe_id, $step_number, $desc);
        $stmt->execute();
    }

    echo '
    <script>
        Swal.fire({
            title: "Success!",
            text: "Your recipe has been created.",
            icon: "success",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#28a745",
            confirmButtonText: "Go to My Recipes",
            cancelButtonText: "Stay Here",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to my_recipes.php if "Go to My Recipes" is clicked
                window.location.href = "my_recipes.php";
            }
            // If "Stay Here" is clicked, just close the dialog
        });
    </script>';

}
?>

<?php include 'footer.php'; ?>
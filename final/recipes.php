<?php 
include 'header.php'; 
include 'db.php';
include 'auth.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Allison&display=swap" rel="stylesheet">

<style>
    html, body {
        height: 100%;
        margin: 0;
    }

    .page-wrapper {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: #f2f2f2;
        text-align: center;
        padding: 40px 20px;
        margin-top: -30px;
    }

    .page-title {
        font-family: 'Allison', cursive;
        font-size: 80px;
        margin-bottom: 20px;
        color: #333;
    }

    .card-container {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 50px;
        flex-wrap: wrap;
        padding: 10px;
    }

    .gradient-one {
        background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    }

    .gradient-two {
        background: linear-gradient(135deg, #a1c4fd 0%,rgb(88, 152, 182) 100%);
    }

    .recipe-card {
        width: 400px;
        height: 350px;
        background-color: white;
        border-radius: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        padding: 60px 30px;
        transition: transform 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: #333333;
    }

    .recipe-card:hover {
        transform: translateY(-10px);
    }

    .recipe-card i {
        font-size: 60px;
        color: #4CAF50;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .recipe-card h3 {
        margin: 10px 0;
        font-weight:bold;
        font-size: 30px;
        margin-bottom: 40px;
    }

    .recipe-card p {
        font-style: italic;
        font-size: 15px;
    }

</style>

<div class="page-wrapper">
    <h2 class="page-title">"Simple Recipes, Endless Flavor."</h2>

    <div class="card-container">
        <a href="add_recipe.php" class="recipe-card gradient-one">
            <i class="fas fa-plus-circle"></i>
            <h3>Create New Recipe</h3>
            <p>Add your own delicious recipes to the collection.</p>
        </a>

        <a href="my_recipes.php" class="recipe-card gradient-two">
            <i class="fas fa-book"></i>
            <h3>My Recipes</h3>
            <p>View, edit, or manage the recipes you’ve added.</p>
        </a>
    </div>
</div>

<?php 
include 'footer.php'; 
?>

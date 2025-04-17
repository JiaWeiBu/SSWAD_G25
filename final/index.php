<?php include 'header.php'; ?>

<section class="hero">
    <div class="container">
        <h1>Welcome to Recipe App</h1>
        <p class="lead">Your ultimate platform for recipes, meal planning, community engagement, and cooking competitions</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-primary btn-lg">Join Now</a>
        <?php else: ?>
            
        <?php endif; ?>
    </div>
</section>

<div class="container mt-3"> <!-- Reduced from mt-5 to mt-3 -->
    <div class="row">
        <div class="col-md-3">
            <div class="feature-box">
                <h3>Recipe Management</h3>
                <p>Create, store, and manage your favorite recipes all in one place.</p>
                <a href="recipes.php" class="btn btn-outline-primary btn-sm">Explore Recipes</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box">
                <h3>Meal Planning</h3>
                <p>Plan your meals ahead of time and never worry about what to cook.</p>
                <a href="meal_planning.php" class="btn btn-outline-primary btn-sm">Plan Meals</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box">
                <h3>Community</h3>
                <p>Connect with other food enthusiasts, share tips, and learn new techniques.</p>
                <a href="community.php" class="btn btn-outline-primary btn-sm">Join Discussions</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-box">
                <h3>Competitions</h3>
                <p>Participate in cooking competitions and showcase your culinary skills.</p>
                <a href="competitions.php" class="btn btn-outline-primary btn-sm">Enter Competitions</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php 
include 'header.php'; 
include 'auth.php';


?>

<link rel="stylesheet" href="style_recipe.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="recipe-body">
    <div class="recipe-container">
        <h2 style="text-align:center" class="page-title">My Recipes</h2>

        <div class="search-filter-container row">
            <?php
            // Get distinct cuisines
            $cuisine_stmt = $conn->prepare("SELECT DISTINCT cuisine FROM recipes WHERE user_id = ?");
            $cuisine_stmt->bind_param("i", $_SESSION['user_id']);
            $cuisine_stmt->execute();
            $cuisines = $cuisine_stmt->get_result();
            ?>

            <select id="cuisineFilter" class="cuisine-filter col-6">
                <option value="">All Cuisines</option>
                <?php while($cuisine = $cuisines->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($cuisine['cuisine']) ?>">
                        <?= htmlspecialchars($cuisine['cuisine']) ?>
                    </option>
                <?php endwhile; ?>
            </select>


            <!-- Live Search Form -->
            <form class="search-form col-5" onsubmit="return false;">

                <input type="text" id="searchInput" placeholder="Search by recipe title...">
                <button type="submit"><i class="fas fa-search"></i></button>
                <a href="#" class="reset-button" title="Reset search" onclick="resetSearch()">
                    <i class="fas fa-times-circle"></i>
                </a>
            </form>
        </div>

        <!-- Recipe Cards Slider -->
        <div class="recipe-slider-wrapper">
            <div class="recipe-slider-container">
                <button class="scroll-button left" id="scrollLeft"><i class="fas fa-chevron-left"></i></button>

                <div class="recipe-card-container" id="recipeSlider">
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("SELECT id, name, cuisine, image FROM recipes WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="recipe-card">';
                            echo '<div class="recipe-card-img">';
                            if ($row['image']) {
                                echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Recipe Image">';
                            } else {
                                echo '<img src="default-image.jpg" alt="Default Recipe Image">';
                            }
                            echo '</div>';
                            echo '<div class="recipe-card-details">';
                            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                            echo '<p><strong>Cuisine:</strong> ' . htmlspecialchars($row['cuisine']) . '</p>';
                            echo '<a href="view_recipe.php?id=' . $row['id'] . '" class="view-recipe-btn">View Recipe</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p style="text-align:center; width: 100%;">You have no recipes yet.</p>';
                    }
                    ?>
                </div>

                <button class="scroll-button right" id="scrollRight"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

        <!-- Expand/Collapse Button -->
        <div class="toggle-view-container" style="text-align:center; margin-top: 10px;">
            <button id="toggleViewBtn" class="toggle-view-btn">Expand</button>
        </div>

        <button id="backToTopBtn" title="Go to top">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>
</div>

<script>
    const cards = document.querySelectorAll('.recipe-card');
    const slider = document.getElementById('recipeSlider');
    const scrollLeftBtn = document.getElementById('scrollLeft');
    const scrollRightBtn = document.getElementById('scrollRight');
    const toggleBtn = document.getElementById('toggleViewBtn');
    const outerWrapper = document.querySelector('.recipe-slider-wrapper');
    const backToTopBtn = document.getElementById("backToTopBtn");

    window.onscroll = () => {
        backToTopBtn.style.display = (window.scrollY > 200) ? "block" : "none";
    };

    backToTopBtn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });

    if (cards.length <= 3) {
        scrollLeftBtn.style.display = 'none';
        scrollRightBtn.style.display = 'none';
        toggleBtn.style.display = 'none';
    } else {
        scrollLeftBtn.style.display = 'block';
        scrollRightBtn.style.display = 'block';
        toggleBtn.style.display = 'inline-block';
    }

    if (cards.length < 3) {
        slider.style.justifyContent = 'center';
    } else {
        slider.style.justifyContent = 'flex-start';
    }

    scrollLeftBtn.addEventListener('click', () => {
        slider.scrollBy({ left: -280 * 3, behavior: 'smooth' });
    });

    scrollRightBtn.addEventListener('click', () => {
        slider.scrollBy({ left: 280 * 3, behavior: 'smooth' });
    });

    toggleBtn.addEventListener('click', () => {
        const isExpanded = slider.classList.toggle('expanded');
        outerWrapper.classList.toggle('expanded');
        toggleBtn.textContent = isExpanded ? 'Collapse' : 'Expand';

        if (isExpanded) {
            scrollLeftBtn.style.display = 'none';
            scrollRightBtn.style.display = 'none';
        } else {
            const cardCount = document.querySelectorAll('.recipe-card').length;
            if (cardCount > 3) {
                scrollLeftBtn.style.display = 'block';
                scrollRightBtn.style.display = 'block';
            }
        }
    });

    // Live Search AJAX
    const searchInput = document.getElementById("searchInput");
    const cuisineFilter = document.getElementById("cuisineFilter");

    function performSearch() {
        const query = searchInput.value.trim();
        const cuisine = cuisineFilter.value;

        fetch(`search_recipe.php?query=${encodeURIComponent(query)}&cuisine=${encodeURIComponent(cuisine)}&t=${Date.now()}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json'
            }
        })
    
        .then(response => {
            if (!response.ok) {
                throw new Error("Not authorized or failed to fetch.");
            }
            return response.text();
        })
        .then(html => {
            slider.innerHTML = html;
            updateCardLayout();
        })
        .catch(error => {
            slider.innerHTML = '<p style="text-align:center;">Error loading recipes</p>';
            console.error("Fetch error:", error);
        });
    }

    searchInput.addEventListener("input", performSearch);
    cuisineFilter.addEventListener("change", performSearch);

    function resetSearch() {
        searchInput.value = "";
        cuisineFilter.value = "";
        performSearch();
    }

    function updateCardLayout() {
        const cards = document.querySelectorAll('.recipe-card');
        if (cards.length <= 3) {
            scrollLeftBtn.style.display = 'none';
            scrollRightBtn.style.display = 'none';
            toggleBtn.style.display = 'none';
        } else {
            scrollLeftBtn.style.display = 'block';
            scrollRightBtn.style.display = 'block';
            toggleBtn.style.display = 'inline-block';
        }

        if (cards.length < 3) {
            slider.style.justifyContent = 'center';
        } else {
            slider.style.justifyContent = 'flex-start';
        }
    }
</script>

<?php include 'footer.php'; ?>

<?php
require_once 'db.php'; // Include database connection
include 'header.php'; // Include header for navigation and session handling

$competitionId = $_GET['id'] ?? null; // Get competition ID from query parameter

if (!$competitionId) {
    // Display error if competition ID is not provided
    echo "<div class='container mt-3'><p class='text-danger'>Competition not found.</p></div>";
    include 'footer.php'; // Include footer
    exit();
}

// Fetch competition details from the database
$stmt = $db->prepare("SELECT * FROM Competitions WHERE id = ?");
$stmt->bind_param("i", $competitionId);
$stmt->execute();
$competition = $stmt->get_result()->fetch_assoc();

if (!$competition) {
    // Display error if competition is not found
    echo "<div class='container mt-3'><p class='text-danger'>Competition not found.</p></div>";
    include 'footer.php'; // Include footer
    exit();
}

// Fetch competition entries with like and comment counts
$stmt = $db->prepare("SELECT competition_entries.id AS entry_id, competition_entries.title, competition_entries.description, 
                      recipes.name AS recipe_name, recipes.id AS recipe_id, users.username, 
                      COUNT(DISTINCT likes.id) AS like_count,
                      COUNT(DISTINCT competition_entries_comments.id) AS comment_count
                      FROM competition_entries
                      JOIN recipes ON competition_entries.recipe_id = recipes.id
                      JOIN users ON competition_entries.user_id = users.id
                      LEFT JOIN likes ON competition_entries.id = likes.entry_id
                      LEFT JOIN competition_entries_comments ON competition_entries.id = competition_entries_comments.entry_id
                      WHERE competition_entries.competition_id = ?
                      GROUP BY competition_entries.id
                      ORDER BY like_count DESC");
$stmt->bind_param("i", $competitionId);
$stmt->execute();
$entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-3">
    <!-- Back to Competitions Button -->
    <div class="mb-3">
        <a href="competitions.php" class="btn btn-outline-primary">← Back to Competitions</a>
    </div>

    <!-- Display competition details -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-center text-primary"><?php echo htmlspecialchars($competition['title']); ?></h2>
            <hr>
            <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($competition['description']); ?></p>
            <p class="card-text"><strong>Start Date:</strong> <?php echo htmlspecialchars($competition['start_date']); ?></p>
            <p class="card-text"><strong>End Date:</strong> <?php echo htmlspecialchars($competition['end_date']); ?></p>
            <p class="card-text">
                <strong>Status:</strong>
                <?php
                $currentDate = date('Y-m-d');
                $isOngoing = false;
                if ($currentDate < $competition['start_date']) {
                    echo "<span class='badge bg-warning text-dark'>Upcoming</span>";
                } elseif ($currentDate > $competition['end_date']) {
                    echo "<span class='badge bg-secondary'>Completed</span>";
                } else {
                    echo "<span class='badge bg-success'>Ongoing</span>";
                    $isOngoing = true;
                }
                ?>
            </p>
        </div>
    </div>

    <!-- Buttons for submit entry, edit, and delete competition -->
    <?php if ($isOngoing || (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $competition['created_by'])): ?>
        <div class="text-end mb-3 d-flex justify-content-end gap-2">
            <?php if ($isOngoing): ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="competition_submit_entry.php?competition_id=<?php echo $competitionId; ?>" class="btn btn-success">Submit Entry</a>
                <?php else: ?>
                    <button class="btn btn-success" onclick="promptSignIn()">Submit Entry</button>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $competition['created_by']): ?>
                <a href="competition_edit.php?id=<?php echo $competitionId; ?>" class="btn btn-warning">Edit Competition</a>
                <button class="btn btn-danger" onclick="deleteCompetition('<?php echo $competitionId; ?>')">Delete Competition</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h3 class="mt-4">Entries</h3>
    <?php if (empty($entries)): ?>
        <!-- Display message if no entries are found -->
        <p class="text-muted">No entries yet.</p>
    <?php else: ?>
        <div class="row">
            <?php 
            $rank = 1; // Initialize rank
            $previousLikeCount = null; // Track the previous like count
            $tieCount = 0; // Track the number of tied entries

            foreach ($entries as $index => $entry): 
                // Check if the user has liked the entry
                $liked = false;
                if (isLoggedIn()) {
                    $stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND entry_id = ?");
                    $stmt->bind_param("ii", $_SESSION['user_id'], $entry['entry_id']);
                    $stmt->execute();
                    $liked = $stmt->get_result()->num_rows > 0;
                }

                // Determine rank based on like count
                if ($previousLikeCount !== null && $entry['like_count'] < $previousLikeCount) {
                    $rank += $tieCount + 1; // Increment rank, skipping ranks for ties
                    $tieCount = 0; // Reset tie count
                } elseif ($previousLikeCount === $entry['like_count']) {
                    $tieCount++; // Increment tie count for tied entries
                }

                $previousLikeCount = $entry['like_count']; // Update previous like count

                // Determine card background color and badge based on rank
                $cardStyle = '';
                $badge = '';
                if (!$isOngoing && $currentDate > $competition['end_date']) {
                    if ($rank === 1) {
                        $cardStyle = 'background-color: #fff8dc;'; // Light Gold
                        $badge = '<span class="badge bg-warning text-dark">1st Place</span>';
                    } elseif ($rank === 2) {
                        $cardStyle = 'background-color: #f8f9fa;'; // Light Silver
                        $badge = '<span class="badge bg-secondary">2nd Place</span>';
                    } elseif ($rank === 3) {
                        $cardStyle = 'background-color: #e6c7a0;'; // Light Bronze
                        $badge = '<span class="badge" style="background-color: #cd7f32; color: white;">3rd Place</span>';
                    }
                } elseif ($isOngoing) {
                    if ($rank === 1) {
                        $cardStyle = 'background-color: #fff8dc;'; // Light Gold
                        $badge = '<span class="badge bg-warning text-dark">Now 1st Place</span>';
                    } elseif ($rank === 2) {
                        $cardStyle = 'background-color: #f8f9fa;'; // Light Silver
                        $badge = '<span class="badge bg-secondary">Now 2nd Place</span>';
                    } elseif ($rank === 3) {
                        $cardStyle = 'background-color: #e6c7a0;'; // Light Bronze
                        $badge = '<span class="badge" style="background-color: #cd7f32; color: white;">Now 3rd Place</span>';
                    }
                }

                // Determine if the like button should be disabled
                $likeButtonDisabled = !$isOngoing && $currentDate > $competition['end_date'];
            ?>
                <div class="col-md-4 mb-3">
                    <!-- Display entry details -->
                    <div class="card h-100 entry-box" style="<?php echo $cardStyle; ?>" onclick="viewEntryDetails('<?php echo $entry['entry_id']; ?>', '<?php echo $competitionId; ?>')">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($entry['title']); ?> <?php echo $badge; ?></h5>
                            <p class="card-text text-muted small">Submitted by: <?php echo htmlspecialchars($entry['username']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($entry['description']); ?></p>
                            <div class="recipe-box mt-3 p-2 border rounded" onclick="event.stopPropagation(); viewRecipe('<?php echo $entry['recipe_id']; ?>')">
                                <h6 class="text-primary">Recipe: <?php echo htmlspecialchars($entry['recipe_name']); ?></h6>
                                <p class="text-muted small">Click to view full recipe</p>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <!-- Like Button -->
                                <button class="btn btn-like me-2 <?php echo $likeButtonDisabled ? 'disabled-like' : ''; ?>" 
                                        onclick="<?php echo $likeButtonDisabled ? 'event.preventDefault()' : "event.stopPropagation(); toggleLike('{$entry['entry_id']}', " . json_encode($liked) . ")"; ?>" 
                                        title="<?php echo $likeButtonDisabled ? 'This competition has ended. You can no longer like entries.' : ''; ?>">
                                    <?php echo $liked ? '❤️' : '🤍'; ?> <?php echo $entry['like_count']; ?>
                                </button>
                                <!-- Comment Button -->
                                <button class="btn btn-comment me-2" onclick="event.stopPropagation(); viewEntryDetails('<?php echo $entry['entry_id']; ?>', '<?php echo $competitionId; ?>')">
                                    💬 <?php echo $entry['comment_count']; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Styling for entry cards */
    .entry-box {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .entry-box:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-like, .btn-comment {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 14px;
        color: #495057;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-like:hover, .btn-comment:hover {
        background-color: #e2e6ea;
        color: #212529;
    }

    .card-text.text-muted.small {
        font-size: 0.85rem;
    }

    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .popup-content button {
        margin: 5px;
    }

    /* Tooltip styling for disabled-like button */
    .disabled-like {
        pointer-events: auto; /* Allow hover and click events */
        cursor: not-allowed;
    }

    .disabled-like:hover::after {
        content: attr(title);
        position: absolute;
        top: -50px; /* Adjusted for larger tooltip */
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: #fff;
        padding: 15px 20px; /* Increased padding for larger tooltip */
        border-radius: 10px; /* Larger border radius */
        font-size: 16px; /* Increased font size */
        white-space: nowrap;
        z-index: 10;
    }
</style>

<script>
    /**
     * Toggle like for an entry.
     * If the user is not logged in, show a popup to prompt login.
     */
    function toggleLike(entryId, isLiked) {
        if (!<?php echo json_encode(isLoggedIn()); ?>) {
            const popup = document.createElement('div');
            popup.innerHTML = `
                <div class="popup-overlay">
                    <div class="popup-content">
                        <p>You need to sign in to like this entry.</p>
                        <button onclick="window.location.href='login.php'" class="btn btn-primary">Sign In</button>
                        <button onclick="this.parentElement.parentElement.remove()" class="btn btn-secondary">Cancel</button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'toggle_like.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                location.reload(); // Refresh the page to show updated like count
            } else {
                alert('An error occurred. Please try again.');
            }
        };
        xhr.send(`entry_id=${entryId}&is_liked=${isLiked}`);
    }

    /**
     * Redirect to entry details page with competition ID.
     */
    function viewEntryDetails(entryId, competitionId) {
        window.location.href = `competition_entry_details.php?id=${entryId}&competition_id=${competitionId}`;
    }

    /**
     * Redirect to recipe details page.
     */
    function viewRecipe(recipeId) {
        window.location.href = `recipe_details.php?id=${recipeId}`;
    }

    /**
     * Delete the competition.
     * If the user confirms, send a request to delete the competition.
     */
    function deleteCompetition(competitionId) {
        if (confirm('Are you sure you want to delete this competition? This action cannot be undone.')) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'competition_delete_entry.php', true); // Updated file reference
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    window.location.href = 'competitions.php'; // Redirect to competitions page after deletion
                } else {
                    alert('An error occurred. Please try again.');
                }
            };
            xhr.send(`competition_id=${competitionId}`);
        }
    }

    /**
     * Prompt the user to sign in when attempting to submit an entry.
     */
    function promptSignIn() {
        const popup = document.createElement('div');
        popup.innerHTML = `
            <div class="popup-overlay">
                <div class="popup-content">
                    <p>You need to sign in to submit an entry.</p>
                    <button onclick="window.location.href='login.php'" class="btn btn-primary">Sign In</button>
                    <button onclick="this.parentElement.parentElement.remove()" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
    }
</script>
</html>

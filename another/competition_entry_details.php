<?php
/**
 * This file displays the details of a competition entry.
 * It shows entry information, associated recipe, and comments.
 * 
 * Key Features:
 * - Fetches entry details and associated recipe.
 * - Displays comments and allows users to add/edit/delete comments.
 * - Provides options to like the entry or edit/delete it for the author.
 */
require_once 'db.php';
include 'header.php';

$entryId = $_GET['id'] ?? null; // Changed 'entry_id' to 'id'
$competitionId = $_GET['competition_id'] ?? null; // Get competition ID from query parameter
$returnUrl = $_GET['returnUrl'] ?? null; // Get the returnUrl parameter from the query string

if (!$entryId) {
    echo "<div class='container mt-3'><p class='text-danger'>Error: Entry ID is missing in the URL.</p></div>";
    include 'footer.php';
    exit();
}

// Debugging: Output the entry ID
// Uncomment the line below for debugging purposes
// echo "<p>Debug: Entry ID is $entryId</p>";

// Fetch entry details including recipe information
$stmt = $db->prepare("SELECT competition_entries.title, competition_entries.description, competition_entries.submission, 
                      recipes.name AS recipe_name, recipes.id AS recipe_id, competition_entries.user_id
                      FROM competition_entries
                      JOIN recipes ON competition_entries.recipe_id = recipes.id
                      WHERE competition_entries.id = ?");
$stmt->bind_param("i", $entryId);
$stmt->execute();
$entryDetails = $stmt->get_result()->fetch_assoc();

if (!$entryDetails) {
    echo "<div class='container mt-3'><p class='text-danger'>Error: No entry found for ID $entryId.</p></div>";
    include 'footer.php';
    exit();
}

// Fetch competition details to check if it has completed
$stmt = $db->prepare("SELECT end_date FROM Competitions WHERE id = ?");
$stmt->bind_param("i", $competitionId);
$stmt->execute();
$competition = $stmt->get_result()->fetch_assoc();
$competitionCompleted = isset($competition['end_date']) && date('Y-m-d') > $competition['end_date'];

// Check if the user has liked the entry
$liked = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT id FROM likes WHERE user_id = ? AND entry_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $entryId);
    $stmt->execute();
    $liked = $stmt->get_result()->num_rows > 0;
}

// Fetch comments for the entry
$stmt = $db->prepare("SELECT competition_entries_comments.id, competition_entries_comments.content, users.username, competition_entries_comments.created_at
                      FROM competition_entries_comments
                      JOIN users ON competition_entries_comments.user_id = users.id
                      WHERE competition_entries_comments.entry_id = ?
                      ORDER BY competition_entries_comments.created_at DESC");
$stmt->bind_param("i", $entryId);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;
    $commentId = $_POST['comment_id'] ?? null;

    if (!$userId) {
        echo "<div class='container mt-3'><p class='text-danger'>You must be logged in to comment.</p></div>";
        include 'footer.php';
        exit();
    }

    if (!empty($content)) {
        if ($commentId) {
            // Update existing comment
            $stmt = $db->prepare("UPDATE competition_entries_comments SET content = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $content, $commentId, $userId);
        } else {
            // Insert new comment
            $stmt = $db->prepare("INSERT INTO competition_entries_comments (entry_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $entryId, $userId, $content);
        }
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$entryId&competition_id=$competitionId&returnUrl=" . urlencode($returnUrl)); // Include competition_id and returnUrl in the URL
        exit();
    }
}
?>

<div class="container mt-3">
    <!-- Back to Competition Details Button -->
    <div class="mb-3">
        <a href="<?php echo $returnUrl ? htmlspecialchars($returnUrl) : "competition_details.php?id=$competitionId"; ?>" 
           class="btn btn-outline-primary">← Back to Competition Details</a>
    </div>

    <h2><?php echo htmlspecialchars($entryDetails['title']); ?></h2>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($entryDetails['description']); ?></p>
    <p><strong>Submission:</strong> <?php echo htmlspecialchars($entryDetails['submission']); ?></p>
    <div class="recipe-box mt-3 p-3 border rounded" onclick="viewRecipe('<?php echo $entryDetails['recipe_id']; ?>')">
        <h5 class="text-primary">Recipe: <?php echo htmlspecialchars($entryDetails['recipe_name']); ?></h5>
        <p class="text-muted small" style="font-size: 0.69rem;">Click to view full recipe</p>
    </div>

    <!-- Edit and Delete Buttons for Entry -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $entryDetails['user_id']): ?>
        <div class="mt-3">
            <a href="competition_edit_entry.php?id=<?php echo $entryId; ?>&competition_id=<?php echo $competitionId; ?>&returnUrl=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
               class="btn btn-secondary">Edit Entry</a>
            <button class="btn btn-danger" onclick="deleteEntry(<?php echo $entryId; ?>)">Delete Entry</button>
        </div>
    <?php endif; ?>

    <!-- Like Button -->
    <div class="mt-3">
        <button class="btn btn-like" 
                onclick="<?php echo $competitionCompleted ? 'event.preventDefault()' : "toggleLike('$entryId', " . json_encode($liked) . ")"; ?>" 
                class="<?php echo $competitionCompleted ? 'disabled-like' : ''; ?>"
                title="<?php echo $competitionCompleted ? 'This competition has ended. You can no longer like entries.' : ''; ?>">
            <?php echo $liked ? '❤️ Liked' : '🤍 Like'; ?>
        </button>
    </div>
</div>

<style>
    /* Styling for disabled-like button */
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
     * Redirect to recipe details page.
     */
    function viewRecipe(recipeId) {
        window.location.href = `recipe_details.php?id=${recipeId}`;
    }

    /**
     * Toggle like for the entry.
     * If the user is not logged in, show a popup to prompt login.
     */
    function toggleLike(entryId, isLiked) {
        if (!<?php echo json_encode(isset($_SESSION['user_id'])); ?>) {
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
        xhr.open('POST', 'competition_toggle_like.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                location.reload(); // Refresh the page to show updated like status
            } else {
                alert('An error occurred. Please try again.');
            }
        };
        xhr.send(`entry_id=${entryId}&is_liked=${isLiked}`);
    }

    /**
     * Send a request to delete the entry.
     */
    function deleteEntry(entryId) {
        if (confirm('Are you sure you want to delete this entry?')) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'competition_delete_entry.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    alert('Entry deleted successfully.');
                    window.location.href = 'competition_details.php?id=<?php echo $competitionId; ?>'; // Redirect to competition details
                } else {
                    alert('An error occurred. Please try again.');
                }
            };
            xhr.send(`entry_id=${entryId}`);
        }
    }
</script>

<div class="container mt-3">
    <h2>Competition Entry Comments</h2>
    <!-- Form for adding a new comment -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <p class="text-danger">You must be logged in to comment.</p>
        <a href="login.php" class="btn btn-primary">Sign In</a>
    <?php else: ?>
        <form method="post" id="addCommentForm">
            <input type="hidden" name="comment_id" id="comment_id" value=""> <!-- Hidden field for comment ID -->
            <textarea name="content" id="comment_content" class="form-control mb-3" placeholder="Add a comment..." required></textarea>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <!-- Hidden form for editing a comment -->
        <form method="post" id="editCommentForm" style="display: none;">
            <input type="hidden" name="comment_id" id="edit_comment_id" value="">
            <textarea name="content" id="edit_comment_content" class="form-control mb-3" placeholder="Edit your comment..." required></textarea>
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
        </form>
    <?php endif; ?>
    <hr>
    <?php if (empty($comments)): ?>
        <p class="text-muted">No comments yet.</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="mb-3">
                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                <small class="text-muted"><?php echo htmlspecialchars($comment['created_at']); ?></small>
                <?php if ($comment['username'] === $_SESSION['username']): ?>
                    <button class="btn btn-sm btn-secondary" onclick="editComment(<?php echo $comment['id']; ?>, '<?php echo htmlspecialchars($comment['content'], ENT_QUOTES); ?>')">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteComment(<?php echo $comment['id']; ?>)">Delete</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    /**
     * Populate the edit form with the comment data for editing.
     */
    function editComment(commentId, content) {
        document.getElementById('edit_comment_id').value = commentId;
        document.getElementById('edit_comment_content').value = content;
        document.getElementById('addCommentForm').style.display = 'none';
        document.getElementById('editCommentForm').style.display = 'block';
        document.getElementById('edit_comment_content').focus(); // Focus on the textarea for editing
    }

    /**
     * Cancel editing and switch back to the add comment form.
     */
    function cancelEdit() {
        document.getElementById('edit_comment_id').value = '';
        document.getElementById('edit_comment_content').value = '';
        document.getElementById('editCommentForm').style.display = 'none';
        document.getElementById('addCommentForm').style.display = 'block';
    }

    /**
     * Send a request to delete a comment.
     */
    function deleteComment(commentId) {
        if (confirm('Are you sure you want to delete this comment?')) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'competition_delete_comment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    location.reload(); // Refresh the page to reflect the deleted comment
                } else {
                    alert('An error occurred. Please try again.');
                }
            };
            xhr.send(`comment_id=${commentId}`);
        }
    }
</script>
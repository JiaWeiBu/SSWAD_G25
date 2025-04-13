<?php
require_once 'db.php';
session_start();

$entryId = $_GET['id'] ?? null;

if (!$entryId) {
    echo "<div class='container mt-3'><p class='text-danger'>Error: Entry ID is missing in the URL.</p></div>";
    exit();
}

// Fetch entry details
$stmt = $db->prepare("SELECT title, description, submission, user_id FROM competition_entries WHERE id = ?");
$stmt->bind_param("i", $entryId);
$stmt->execute();
$entryDetails = $stmt->get_result()->fetch_assoc();

if (!$entryDetails) {
    echo "<div class='container mt-3'><p class='text-danger'>Error: No entry found for ID $entryId.</p></div>";
    exit();
}

// Check if the logged-in user is the author of the entry
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $entryDetails['user_id']) {
    echo "<div class='container mt-3'><p class='text-danger'>You are not authorized to edit this entry.</p></div>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $submission = $_POST['submission'] ?? '';

    if (empty($title) || empty($description) || empty($submission)) {
        echo "<div class='container mt-3'><p class='text-danger'>All fields are required.</p></div>";
    } else {
        $stmt = $db->prepare("UPDATE competition_entries SET title = ?, description = ?, submission = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $submission, $entryId);
        $stmt->execute();

        header("Location: competition_entry_details.php?id=$entryId");
        exit();
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-3">
    <h2>Edit Competition Entry</h2>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($entryDetails['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="5" required><?php echo htmlspecialchars($entryDetails['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="submission" class="form-label">Submission</label>
            <textarea name="submission" id="submission" class="form-control" rows="5" required><?php echo htmlspecialchars($entryDetails['submission']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Entry</button>
        <a href="competition_entry_details.php?id=<?php echo $entryId; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'footer.php'; ?>

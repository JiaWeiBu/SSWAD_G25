<?php
require_once 'db.php';
include 'header.php'; // Include header for navigation and session handling

$competitionId = $_GET['id'] ?? null;

if (!$competitionId) {
    echo "<div class='container mt-3'><p class='text-danger'>Competition not found.</p></div>";
    include 'footer.php';
    exit();
}

// Fetch competition details
$stmt = $db->prepare("SELECT * FROM competitions WHERE id = ?");
$stmt->bind_param("i", $competitionId);
$stmt->execute();
$competition = $stmt->get_result()->fetch_assoc();

if (!$competition) {
    echo "<div class='container mt-3'><p class='text-danger'>Competition not found.</p></div>";
    include 'footer.php';
    exit();
}

// Ensure the logged-in user is signed in and is the creator of the competition
if (!isset($_SESSION['user_id'])) {
    echo "<div class='container mt-3'><p class='text-danger'>You must be logged in to edit this competition.</p></div>";
    echo "<a href='login.php' class='btn btn-primary'>Sign In</a>";
    include 'footer.php';
    exit();
}

if ($_SESSION['user_id'] !== $competition['created_by']) {
    echo "<div class='container mt-3'><p class='text-danger'>You are not authorized to edit this competition.</p></div>";
    include 'footer.php';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    $stmt = $db->prepare("UPDATE competitions SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $competitionId);

    if ($stmt->execute()) {
        header("Location: competition_details.php?id=$competitionId");
        exit();
    } else {
        $error = "Failed to update competition.";
    }
}
?>

<div class="container mt-3">
    <h2>Edit Competition</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($competition['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($competition['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($competition['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($competition['end_date']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="competition_details.php?id=<?php echo $competitionId; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'footer.php'; ?>

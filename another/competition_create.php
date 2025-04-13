<?php
/**
 * This file handles the creation of a new competition.
 * It validates user input, ensures the user is logged in, and inserts the competition into the database.
 * 
 * Key Features:
 * - Validates form inputs (title, description, start date, end date).
 * - Ensures the start date is today or in the future.
 * - Ensures the end date is after the start date.
 * - Inserts the competition into the database.
 * - Redirects to the competitions page on success.
 */

require_once 'db.php'; // Include database connection
include 'header.php'; // Include header for navigation and session handling

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if none is active
}
$current_user_id = $_SESSION['user_id'] ?? null; // Retrieve logged-in user's ID

// Handle form submission for creating a new competition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? ''; // Get competition title
    $description = $_POST['description'] ?? ''; // Get competition description
    $start_date = $_POST['start_date'] ?? ''; // Get competition start date
    $end_date = $_POST['end_date'] ?? ''; // Get competition end date

    if ($current_user_id) { // Ensure user is logged in
        // Check if any field is empty
        if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
            $error = "All fields are required.";
        } else {
            $current_date = date('Y-m-d'); // Get the current date

            // Validate dates
            if ($start_date < $current_date) { // Allow today as a valid start date
                $error = "Start date must be today or in the future.";
            } elseif ($end_date <= $start_date) {
                $error = "End date must be after the start date.";
            } else {
                // Prepare SQL query to insert competition into database
                $stmt = $db->prepare("INSERT INTO Competitions (title, description, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $current_user_id);

                if ($stmt->execute()) {
                    // Redirect to competitions page on success
                    header("Location: competitions.php");
                    exit();
                } else {
                    $error = "Failed to create competition."; // Display error message on failure
                }
            }
        }
    } else {
        $error = "You must be logged in to create a competition."; // Display error if user is not logged in
    }
}
?>

<div class="container mt-3">
    <h2>Create a New Competition</h2>
    <!-- Display error message if any -->
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <!-- Form to create a new competition -->
    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date ?? ''); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
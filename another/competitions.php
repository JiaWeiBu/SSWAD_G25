<?php
require_once 'db.php'; // Include database connection
include 'header.php'; // Include header for navigation and session handling

// Fetch all competitions from the database
$result = $db->query("SELECT Competitions.*, users.username AS creator_username 
                      FROM Competitions 
                      JOIN users ON Competitions.created_by = users.id");
$competitions = $result->fetch_all(MYSQLI_ASSOC);

/**
 * Get the status of a competition based on its start and end dates.
 *
 * @param string $start_date The start date of the competition.
 * @param string $end_date The end date of the competition.
 * @return array An array containing the status text, color, and status category (e.g., "active", "ended", "future").
 */
function getCompetitionStatus($start_date, $end_date) {
    $current_date = new DateTime(); // Get the current date
    $start = new DateTime($start_date); // Convert start date to DateTime object
    $end = new DateTime($end_date); // Convert end date to DateTime object

    // Determine the status of the competition
    if ($current_date < $start) {
        $days_to_start = $current_date->diff($start)->days; // Calculate days until start
        return ["$days_to_start days to start", "blue", "future"]; // Future competition
    } elseif ($current_date >= $start && $current_date <= $end) {
        $days_to_end = $current_date->diff($end)->days; // Calculate days until end
        return ["$days_to_end days left", "green", "active"]; // Active competition
    } else {
        return ["Ended", "red", "ended"]; // Ended competition
    }
}

/**
 * Sort competitions by their status.
 * Active competitions are displayed first, followed by ended competitions, and then future competitions.
 *
 * @param array $a The first competition to compare.
 * @param array $b The second competition to compare.
 * @return int Comparison result for sorting.
 */
usort($competitions, function ($a, $b) {
    $status_order = ["active" => 0, "ended" => 1, "future" => 2]; // Define sorting order
    list(, , $status_a) = getCompetitionStatus($a['start_date'], $a['end_date']); // Get status of first competition
    list(, , $status_b) = getCompetitionStatus($b['start_date'], $b['end_date']); // Get status of second competition
    return $status_order[$status_a] <=> $status_order[$status_b]; // Compare statuses
});
?>

<style>
    /* Add hover effect to cards for better user experience */
    .card-hover {
        transition: transform 0.2s ease-in-out; /* Smooth scaling effect */
    }
    .card-hover:hover {
        transform: scale(1.05); /* Slightly enlarge card on hover */
        cursor: pointer; /* Change cursor to pointer */
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
        border-radius: 5px;
        text-align: center;
    }
</style>

<div class="container mt-3">
    <h2 class="text-center">Competitions</h2>
    <!-- Button to create a new competition -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="competition_create.php" class="btn btn-primary mb-3">Create New Competition</a>
    <?php else: ?>
        <button class="btn btn-primary mb-3" onclick="promptSignIn()">Create New Competition</button>
    <?php endif; ?>
    <div class="row">
        <?php foreach ($competitions as $competition): ?>
            <div class="col-md-4 mb-3">
                <!-- Make the entire card clickable -->
                <a href="competition_details.php?id=<?php echo htmlspecialchars($competition['id']); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 card-hover">
                        <div class="card-body">
                            <!-- Display competition title -->
                            <h5 class="card-title"><?php echo htmlspecialchars($competition['title']); ?></h5>
                            <!-- Display competition description -->
                            <p class="card-text"><?php echo htmlspecialchars($competition['description']); ?></p>
                            <!-- Display the username of the creator -->
                            <p class="text-muted" style="font-size: 0.8em; position: absolute; bottom: 10px; right: 10px;">
                                Posted by: <?php echo htmlspecialchars($competition['creator_username']); ?>
                            </p>
                            <p class="card-text">
                                <strong>Status:</strong>
                                <?php
                                $currentDate = date('Y-m-d');
                                if ($currentDate < $competition['start_date']) {
                                    echo "<span class='badge bg-warning text-dark'>Upcoming</span>";
                                } elseif ($currentDate > $competition['end_date']) {
                                    echo "<span class='badge bg-secondary'>Completed</span>";
                                } else {
                                    echo "<span class='badge bg-success'>Ongoing</span>";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    /**
     * Prompt the user to sign in when attempting to create a competition.
     */
    function promptSignIn() {
        const popup = document.createElement('div');
        popup.innerHTML = `
            <div class="popup-overlay">
                <div class="popup-content">
                    <p>You need to sign in to create a competition.</p>
                    <button onclick="window.location.href='login.php'" class="btn btn-primary">Sign In</button>
                    <button onclick="this.parentElement.parentElement.remove()" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
    }
</script>

<?php
// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your database username
define('DB_PASS', ''); // Change this to your database password
define('DB_NAME', 'culinary_db'); // Change this to your database name

// Establish database connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set character set to utf8mb4 for proper encoding
$mysqli->set_charset("utf8mb4");

class Database {
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, 'culinary_db'); // Updated database name

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8mb4");
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function prepare($sql) {
        // Ensure table names are case-sensitive
        return $this->connection->prepare($sql);
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function close() {
        $this->connection->close();
    }

    public function getUserIdByEmail($email) {
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

// Initialize Database Instance
$db = new Database();
?>

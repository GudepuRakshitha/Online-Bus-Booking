/* <?php
// Ensure session is started only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $host = "localhost";
    private $db_name = "bus_system";
    private $username = "root";
    private $password = "";
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            echo "<script>console.log('Database connection successful!');</script>";
        } catch(PDOException $e) {
            echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red; border-radius: 5px;'>
                    Connection Failed: " . $e->getMessage() . 
                 "</div>";
            die();
        }

        return $this->conn;
    }
}

// Create a connection helper function
function connectDB() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}

// Error handling function
function handleDatabaseError($e) {
    error_log("Database Error: " . $e->getMessage());
    return "An error occurred. Please try again later.";
}

// Initialize connection
$database = new Database();
$conn = $database->getConnection();
?> */



<?php
try {
    $host = 'localhost';
    $dbname = 'bus_booking_system';
    $username = 'your_username';
    $password = 'your_password';

    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    session_start();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
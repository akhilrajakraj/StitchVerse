<?php

class DatabaseCon {
    private $dbhost = 'localhost'; // Port is usually not needed here
    private $dbuser = 'root';
    private $dbpass = '';
    private $db = 'stitchverse1'; // Corrected to match your .sql file
    private $conn;

    // The constructor now uses modern error reporting
    public function __construct() {
        // Suppress default warnings to handle errors manually
        mysqli_report(MYSQLI_REPORT_OFF);

        $this->conn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->db);

        // Check for a connection error
        if (mysqli_connect_errno()) {
            die('Database connection failed: ' . mysqli_connect_error());
        }
    }

    /**
     * Executes a SELECT query securely using prepared statements.
     *
     * @param string $query The SQL query with ? placeholders.
     * @param string $types A string containing the types of the parameters (e.g., "s" for string, "i" for integer).
     * @param array ...$params The parameters to bind.
     * @return mysqli_result|false The result object on success, or false on failure.
     */
    public function selectData($query, $types = "", ...$params) {
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->conn->error));
        }

        if ($types != "" && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Executes an INSERT, UPDATE, or DELETE query securely using prepared statements.
     *
     * @param string $query The SQL query with ? placeholders.
     * @param string $types A string containing the types of the parameters (e.g., "s", "i", "d").
     * @param array ...$params The parameters to bind.
     * @return int|false The number of affected rows, or false on failure.
     */
    public function executeQuery($query, $types = "", ...$params) {
        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->conn->error));
        }

        if ($types != "" && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->affected_rows;
    }

    /**
     * Returns the raw mysqli connection object.
     * Useful for transactions or other specific mysqli functions.
     */
    public function getConnection() {
        return $this->conn;
    }

    // The destructor automatically closes the connection when the script ends
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
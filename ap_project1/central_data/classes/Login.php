<?php
session_start();
require_once __DIR__ . "/../config/Database.php";

class Login {
    private $conn;
    private $table = "LOGIN";

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConn();
        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function authenticate($username, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE LOGIN_USER = ? AND LOGIN_PASS = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}

?>

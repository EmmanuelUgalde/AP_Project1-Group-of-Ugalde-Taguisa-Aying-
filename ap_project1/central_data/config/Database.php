<?php 
class Database {

    private $host = "localhost";
    private $user = "root";
    private $pass = "dealwiththeletters12";
    private $dbname = "medical";

    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conn->connect_error) {
            die ("Connection failed . " . $this->conn->connect_error);
        }
    }

    public function getConn() {
        return $this->conn;
    }


}



?>
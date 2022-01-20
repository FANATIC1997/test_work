<?php
class Database {
    private $host = "192.168.0.2";
    private $db_name = "db_work";
    private $username = "root";
    private $password = "127000";
    public $conn;

    public function getConnection(): mysqli
    {
        $this->conn = null;

        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
           die("Connection failed: " . $this->conn->connect_error);
        }
        return $this->conn;
    }
}
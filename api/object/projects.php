<?php
class Projects {


    private $conn;
    private $table_name = "projects";


    public $id;
    public $url;
    public $result;



    public function __construct($db){
        $this->conn = $db;
    }


    function read(){
        $sql = "SELECT * FROM ".$this->table_name;
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function find($url)
    {
        $result_search = $this->conn->query('SELECT * FROM '.$this->table_name.' WHERE url="'.$url.'"');
        return $result_search->fetch_assoc();
    }

    function create($url, $string){
        try {
            $this->conn->query("INSERT INTO '.$this->table_name.'(url, result) VALUES ('".$url."', '".$string."')");
            return true;
        }
        catch (mysqli_sql_exception $exception)
        {
            die($exception);
        }
    }
}
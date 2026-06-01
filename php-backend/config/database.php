<?php

class Database
{
    private $host = "localhost";
    private $db_name = "new_project";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
 
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Não expor mensagens de erro do banco diretamente na resposta HTML.
            $this->conn = null;
        }

        return $this->conn;
    }
}
 
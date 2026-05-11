<?php

class Usuario
{
    private $conn;
    private $table_name = "usuarios";
 
    public $id;
    public $nome;
    public $email;
    public $senha;
    public $tipo;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findByEmail()
    {
        $query = "SELECT id, nome, senha, tipo FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        return $stmt;
    }

    public function emailExisteParaOutroUsuario($idAtual)
    {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email AND id != :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $idAtual);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
<?php
// php-backend/teste.php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db) {
    echo "Sucesso! O backend do Meu Especialista está conectado ao MySQL.";
} else {
    echo "Erro ao conectar.";
}
?>
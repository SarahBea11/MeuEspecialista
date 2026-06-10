<?php
require_once __DIR__ . '/../config/cors.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro de conexão com o banco de dados."]);
    exit();
}

try {
    $query = "SELECT id, nome_convenio AS nome FROM convenios ORDER BY nome_convenio ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $convenios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "dados" => $convenios]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro ao buscar convênios: " . $e->getMessage()]);
}

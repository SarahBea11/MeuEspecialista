<?php
require_once __DIR__ . '/../config/cors.php';
include_once '../config/database.php';
include_once '../config/auth_middleware.php';

$usuarioLogado = verificarAutenticacao();

if (!isset($usuarioLogado->tipo) || $usuarioLogado->tipo !== 'admin') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acesso proibido. Apenas administradores."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro de conexão com o banco de dados."]);
    exit();
}

try {
    $query = "SELECT id, nome, email FROM usuarios WHERE tipo = 'admin' ORDER BY nome ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "dados" => $admins]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro ao listar administradores: " . $e->getMessage()]);
}

<?php
ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
include_once '../config/database.php';
include_once '../config/auth_middleware.php';

$usuarioLogado = verificarAutenticacao();

if (!isset($usuarioLogado->tipo) || $usuarioLogado->tipo !== 'medico') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acesso permitido apenas para médicos."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro de conexão com o banco."]);
    exit();
}

try {
    $id = (int)$usuarioLogado->id;
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM favoritos WHERE medico_usuario_id = :id");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "total_favoritos" => (int)$result['total']]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro ao contar favoritos: " . $e->getMessage()]);
}

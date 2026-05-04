<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}
 
include_once '../config/database.php';
include_once '../config/auth_middleware.php';

$usuarioLogado = verificarAutenticacao();

$database = new Database();
$db = $database->getConnection();

$id = $usuarioLogado->id;
$tipo = $usuarioLogado->tipo;

try {
    $query = "SELECT nome, email, tipo FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $dadosBase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dadosBase) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Usuário não encontrado"]);
        exit;
    }

    $dadosExtra = [];

    if ($tipo === 'medico') {
        $queryMed = "SELECT crm, especialidade, cidade, telefone, endereco FROM medicos_perfil WHERE usuario_id = :id";
        $stmtMed = $db->prepare($queryMed);
        $stmtMed->bindParam(":id", $id);
        $stmtMed->execute();
        $dadosExtra = $stmtMed->fetch(PDO::FETCH_ASSOC) ?: [];
    } else {
        $queryPac = "SELECT p.cpf, p.cidade, p.telefone, p.endereco, c.nome_convenio as convenio 
                     FROM pacientes_perfil p
                     LEFT JOIN convenios c ON p.convenio_id = c.id
                     WHERE p.usuario_id = :id";
        $stmtPac = $db->prepare($queryPac);
        $stmtPac->bindParam(":id", $id);
        $stmtPac->execute();
        $dadosExtra = $stmtPac->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    echo json_encode([
        "status" => "success",
        "dados" => array_merge($dadosBase, $dadosExtra)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
}

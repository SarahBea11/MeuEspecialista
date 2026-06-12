<?php
// Desativado em produção para segurança
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../config/cors.php';

include_once '../config/database.php';
include_once '../config/auth_middleware.php';
include_once '../config/security_helpers.php';

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
        $queryMed = "SELECT crm, especialidade, cidade, telefone, endereco, foto FROM medicos_perfil WHERE usuario_id = :id";
        $stmtMed = $db->prepare($queryMed);
        $stmtMed->bindParam(":id", $id);
        $stmtMed->execute();
        $dadosExtra = $stmtMed->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($dadosExtra)) {
            $dadosExtra['crm'] = decryptData($dadosExtra['crm'], true);
            $dadosExtra['telefone'] = decryptData($dadosExtra['telefone'], false);
        }
    } else if ($tipo === 'paciente') {
        $queryPac = "SELECT p.cpf, p.cidade, p.telefone, p.endereco, p.convenio_id, c.nome_convenio as convenio 
                     FROM pacientes_perfil p
                     LEFT JOIN convenios c ON p.convenio_id = c.id
                     WHERE p.usuario_id = :id";
        $stmtPac = $db->prepare($queryPac);
        $stmtPac->bindParam(":id", $id);
        $stmtPac->execute();
        $dadosExtra = $stmtPac->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($dadosExtra)) {
            $dadosExtra['cpf'] = decryptData($dadosExtra['cpf'], true);
            $dadosExtra['telefone'] = decryptData($dadosExtra['telefone'], false);
        }
    }


    echo json_encode([
        "status" => "success",
        "dados" => array_merge($dadosBase, $dadosExtra)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro: " . $e->getMessage()]);
}

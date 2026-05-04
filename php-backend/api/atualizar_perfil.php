<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/database.php';
include_once '../config/auth_middleware.php';

$usuarioLogado = verificarAutenticacao();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Dados não recebidos."]);
    exit;
}

try {
    $db->beginTransaction();

    $queryUser = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->execute([
        ':nome' => $data->nome,
        ':email' => $data->email,
        ':id' => $usuarioLogado->id
    ]);

    if ($usuarioLogado->tipo === 'medico') {
        $queryExtra = "UPDATE medicos_perfil SET 
                        crm = :crm, 
                        especialidade = :especialidade, 
                        cidade = :cidade, 
                        telefone = :telefone, 
                        endereco = :endereco 
                       WHERE usuario_id = :id";
        $stmtExtra = $db->prepare($queryExtra);
        $stmtExtra->execute([
            ':crm' => $data->crm,
            ':especialidade' => $data->especialidade,
            ':cidade' => $data->cidade,
            ':telefone' => $data->telefone,
            ':endereco' => $data->endereco,
            ':id' => $usuarioLogado->id
        ]);
    } else {
        $queryExtra = "UPDATE pacientes_perfil SET 
                        cpf = :cpf, 
                        cidade = :cidade, 
                        telefone = :telefone, 
                        endereco = :endereco 
                       WHERE usuario_id = :id";
        $stmtExtra = $db->prepare($queryExtra);
        $stmtExtra->execute([
            ':cpf' => $data->cpf,
            ':cidade' => $data->cidade,
            ':telefone' => $data->telefone,
            ':endereco' => $data->endereco,
            ':id' => $usuarioLogado->id
        ]);
    }

    if (!empty($data->senha) && $data->senha === $data->confirmarSenha) {
        $senhaHash = password_hash($data->senha, PASSWORD_BCRYPT);
        $querySenha = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        $stmtSenha = $db->prepare($querySenha);
        $stmtSenha->execute([':senha' => $senhaHash, ':id' => $usuarioLogado->id]);
    }

    $db->commit();
    echo json_encode(["status" => "success", "message" => "Perfil atualizado!"]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

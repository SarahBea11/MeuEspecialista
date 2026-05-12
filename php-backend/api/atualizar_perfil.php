<?php
require_once __DIR__ . '/../config/cors.php';

include_once '../config/database.php';
include_once '../config/auth_middleware.php';

include_once '../models/Usuario.php';

$usuarioLogado = verificarAutenticacao();

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Dados não recebidos."]);
    exit;
}

$nome = isset($data->nome) ? trim($data->nome) : "";
$email = isset($data->email) ? trim($data->email) : "";
$idUsuario = $usuarioLogado->id;

if (empty($nome) || empty($email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nome e E-mail são obrigatórios."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Formato de e-mail inválido."]);
    exit;
}

$usuario->email = $email;
if ($usuario->emailExisteParaOutroUsuario($idUsuario)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Este e-mail já está em uso por outro usuário."]);
    exit;
}

try {
    $db->beginTransaction();

    $queryUser = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':id' => $idUsuario
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
            ':id' => $idUsuario
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
            ':id' => $idUsuario
        ]);
    }

    if (!empty($data->senha)) {
        if (strlen($data->senha) < 6) {
            throw new Exception("A nova senha deve ter pelo menos 6 caracteres.");
        }
        if ($data->senha === ($data->confirmarSenha ?? "")) {
            $senhaHash = password_hash($data->senha, PASSWORD_DEFAULT);
            $querySenha = "UPDATE usuarios SET senha = :senha WHERE id = :id";
            $stmtSenha = $db->prepare($querySenha);
            $stmtSenha->execute([':senha' => $senhaHash, ':id' => $idUsuario]);
        } else {
            throw new Exception("As senhas não coincidem.");
        }
    }

    $db->commit();
    echo json_encode(["status" => "success", "message" => "Perfil atualizado com sucesso!"]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
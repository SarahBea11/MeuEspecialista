<?php
require_once __DIR__ . '/../config/cors.php';

include_once '../models/Usuario.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

$nome = isset($data->nome) ? trim($data->nome) : "";
$email = isset($data->email) ? trim($data->email) : "";
$senha = isset($data->senha) ? $data->senha : "";
$tipo = isset($data->tipo) ? trim($data->tipo) : "";

if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Preencha todos os campos obrigatórios."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "E-mail inválido."]);
    exit();
}

if (strlen($senha) < 6) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "A senha deve ter pelo menos 6 caracteres."]);
    exit();
}

$usuario->email = $email;
$stmtCheck = $usuario->findByEmail();
if ($stmtCheck->rowCount() > 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Este e-mail já está cadastrado."]);
    exit();
}

if ($tipo !== 'medico' && $tipo !== 'paciente') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Tipo de usuário inválido."]);
    exit();
}

try {
    $db->beginTransaction();

    $query = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)";
    $stmt = $db->prepare($query);

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha", $senhaHash);
    $stmt->bindParam(":tipo", $tipo);
    $stmt->execute();

    $usuario_id = $db->lastInsertId();

    if ($tipo === 'medico') {
        if (empty($data->crm) || empty($data->telefone)) {
            throw new Exception("CRM e Telefone são obrigatórios para médicos.");
        }

        $queryMed = "INSERT INTO medicos_perfil (usuario_id, crm, especialidade, telefone, cidade, endereco) 
                     VALUES (:usuario_id, :crm, :especialidade, :telefone, :cidade, :endereco)";

        $stmtMed = $db->prepare($queryMed);
        $stmtMed->bindParam(":usuario_id", $usuario_id);
        $stmtMed->bindParam(":crm", $data->crm);
        $stmtMed->bindParam(":especialidade", $data->especialidade);
        $stmtMed->bindParam(":telefone", $data->telefone);
        $stmtMed->bindParam(":cidade", $data->cidade);
        $stmtMed->bindParam(":endereco", $data->endereco);
        $stmtMed->execute();
    } else if ($tipo === 'paciente') {
        if (empty($data->cpf)) {
            throw new Exception("CPF é obrigatório para pacientes.");
        }

        $queryPac = "INSERT INTO pacientes_perfil (usuario_id, cpf, convenio_id) 
                     VALUES (:usuario_id, :cpf, :convenio_id)";

        $stmtPac = $db->prepare($queryPac);
        $stmtPac->bindParam(":usuario_id", $usuario_id);
        $stmtPac->bindParam(":cpf", $data->cpf);

        $conv_id = !empty($data->convenio_id) ? $data->convenio_id : null;
        $stmtPac->bindParam(":convenio_id", $conv_id);
        $stmtPac->execute();
    }

    $db->commit();
    echo json_encode(["status" => "success", "message" => "Cadastro realizado com sucesso!"]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro interno no servidor: " . $e->getMessage()]);
}

?>
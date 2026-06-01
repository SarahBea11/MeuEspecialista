<?php
require_once __DIR__ . '/../config/cors.php';

include_once '../models/Usuario.php';
include_once '../config/database.php';
include_once '../config/security_helpers.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

function ensureDefaultConvenios($db) {
    $defaults = [
        2 => 'Amil',
        3 => 'Intermédica',
        4 => 'Unimed',
    ];

    $query = "INSERT IGNORE INTO convenios (id, nome_convenio) VALUES (:id, :nome_convenio)";
    $stmt = $db->prepare($query);

    foreach ($defaults as $id => $nome) {
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nome_convenio', $nome, PDO::PARAM_STR);
        $stmt->execute();
    }
}

ensureDefaultConvenios($db);

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

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $senha)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "A senha deve ter pelo menos 8 caracteres, incluir letras, números e símbolo."]);
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

if ($tipo === 'medico') {
    $crm = isset($data->crm) ? trim($data->crm) : '';
    if (empty($crm)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "CRM é obrigatório para médicos."]);
        exit();
    }

    if (!validarCRM($crm)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Formato de CRM inválido. Deve conter de 4 a 10 dígitos (opcionalmente seguido por estado, ex: 12345/SP)."]);
        exit();
    }

    $crmCriptografado = encryptData($crm, true);
    $queryCrm = "SELECT id FROM medicos_perfil WHERE crm = :crm LIMIT 1";
    $stmtCrm = $db->prepare($queryCrm);
    $stmtCrm->bindParam(':crm', $crmCriptografado);
    $stmtCrm->execute();
    if ($stmtCrm->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Este CRM já está cadastrado."]);
        exit();
    }
}

if ($tipo === 'paciente') {
    $cpf = isset($data->cpf) ? trim($data->cpf) : '';
    if (empty($cpf)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "CPF é obrigatório para pacientes."]);
        exit();
    }

    if (!validarCPF($cpf)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "CPF inválido."]);
        exit();
    }

    $cpfCriptografado = encryptData($cpf, true);
    $queryCpf = "SELECT id FROM pacientes_perfil WHERE cpf = :cpf LIMIT 1";
    $stmtCpf = $db->prepare($queryCpf);
    $stmtCpf->bindParam(':cpf', $cpfCriptografado);
    $stmtCpf->execute();
    if ($stmtCpf->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Este CPF já está cadastrado."]);
        exit();
    }
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

        $crmCriptografado = encryptData($data->crm, true);
        $telefoneCriptografado = encryptData($data->telefone, false);

        $stmtMed = $db->prepare($queryMed);
        $stmtMed->bindParam(":usuario_id", $usuario_id);
        $stmtMed->bindParam(":crm", $crmCriptografado);
        $stmtMed->bindParam(":especialidade", $data->especialidade);
        $stmtMed->bindParam(":telefone", $telefoneCriptografado);
        $stmtMed->bindParam(":cidade", $data->cidade);
        $stmtMed->bindParam(":endereco", $data->endereco);
        $stmtMed->execute();
    } else if ($tipo === 'paciente') {
        if (empty($data->cpf)) {
            throw new Exception("CPF é obrigatório para pacientes.");
        }

        $queryPac = "INSERT INTO pacientes_perfil (usuario_id, cpf, convenio_id, cidade, telefone, endereco) 
                     VALUES (:usuario_id, :cpf, :convenio_id, :cidade, :telefone, :endereco)";

        $cpfCriptografado = encryptData($data->cpf, true);
        $telefoneCriptografado = !empty($data->telefone) ? encryptData(trim($data->telefone), false) : null;

        $stmtPac = $db->prepare($queryPac);
        $stmtPac->bindParam(":usuario_id", $usuario_id);
        $stmtPac->bindParam(":cpf", $cpfCriptografado);

        $conv_id  = !empty($data->convenio_id) ? $data->convenio_id : null;
        $cidade   = !empty($data->cidade)      ? trim($data->cidade)   : null;
        $endereco = !empty($data->endereco)    ? trim($data->endereco) : null;

        $stmtPac->bindParam(":convenio_id", $conv_id);
        $stmtPac->bindParam(":cidade",      $cidade);
        $stmtPac->bindParam(":telefone",    $telefoneCriptografado);
        $stmtPac->bindParam(":endereco",    $endereco);
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
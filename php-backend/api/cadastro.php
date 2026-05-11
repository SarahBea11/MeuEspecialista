<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->nome) &&
    !empty($data->email) &&
    !empty($data->senha) &&
    !empty($data->tipo)
) {
    try {
        $db->beginTransaction();

        // 1. Inserir na tabela usuarios
        $query = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)";
        $stmt = $db->prepare($query);

        $senhaHash = password_hash($data->senha, PASSWORD_DEFAULT);

        $stmt->bindParam(":nome", $data->nome);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":senha", $senhaHash);
        $stmt->bindParam(":tipo", $data->tipo);
        $stmt->execute();

        $usuario_id = $db->lastInsertId();

        // 2. Se for médico, preenche medicos_perfil
        if ($data->tipo === 'medico') {
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
        } 
        // 3. Se for paciente, preenche pacientes_perfil
        else if ($data->tipo === 'paciente') {
            if (empty($data->cpf)) {
                throw new Exception("CPF é obrigatório para pacientes.");
            }

            $queryPac = "INSERT INTO pacientes_perfil (usuario_id, cpf, convenio_id) 
                         VALUES (:usuario_id, :cpf, :convenio_id)";
            
            $stmtPac = $db->prepare($queryPac);
            $stmtPac->bindParam(":usuario_id", $usuario_id);
            $stmtPac->bindParam(":cpf", $data->cpf);
            
            // Caso o convênio não seja selecionado, envia NULL
            $conv_id = !empty($data->convenio_id) ? $data->convenio_id : null;
            $stmtPac->bindParam(":convenio_id", $conv_id);
            $stmtPac->execute();
        }

        $db->commit();
        echo json_encode(["status" => "success", "message" => "Cadastro realizado com sucesso!"]);

    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Dados incompletos."]);
}
?>
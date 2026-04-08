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

        $senhaHash = password_hash($data->senha, PASSWORD_DEFAULT);

        $query = "INSERT INTO usuarios (nome, email, senha, tipo)
                  VALUES (:nome, :email, :senha, :tipo)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":nome", $data->nome);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":senha", $senhaHash);
        $stmt->bindParam(":tipo", $data->tipo);

        $stmt->execute();

        $usuario_id = $db->lastInsertId();

        if ($data->tipo === 'medico') {

            if (
                empty($data->crm) ||
                empty($data->especialidade) ||
                empty($data->telefone) ||
                empty($data->cidade) ||
                empty($data->endereco)
            ) {
                throw new Exception("Dados incompletos para médico.");
            }

            $query = "INSERT INTO medicos_perfil 
                      (usuario_id, crm, especialidade, telefone, cidade, endereco)
                      VALUES (:usuario_id, :crm, :especialidade, :telefone, :cidade, :endereco)";

            $stmt = $db->prepare($query);

            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->bindParam(":crm", $data->crm);
            $stmt->bindParam(":especialidade", $data->especialidade);
            $stmt->bindParam(":telefone", $data->telefone);
            $stmt->bindParam(":cidade", $data->cidade);
            $stmt->bindParam(":endereco", $data->endereco);

            $stmt->execute();
        }

        if ($data->tipo === 'paciente') {

            if (empty($data->cpf)) {
                throw new Exception("CPF é obrigatório para paciente.");
            }

            $query = "INSERT INTO pacientes_perfil 
                      (usuario_id, cpf, convenio_id)
                      VALUES (:usuario_id, :cpf, :convenio_id)";

            $stmt = $db->prepare($query);

            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->bindParam(":cpf", $data->cpf);

            $convenio_id = isset($data->convenio_id) ? $data->convenio_id : null;
            $stmt->bindParam(":convenio_id", $convenio_id);

            $stmt->execute();
        }

        $db->commit();

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Cadastro realizado com sucesso"
        ]);

    } catch (Exception $e) {

        $db->rollBack();

        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Dados incompletos"
    ]);
}


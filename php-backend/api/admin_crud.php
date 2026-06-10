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

$data = json_decode(file_get_contents("php://input"));
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    // Cidades CRUD
    case 'save_cidade':
        $nome = isset($data->nome) ? trim($data->nome) : '';
        $id = isset($data->id) ? (int)$data->id : 0;
        if (empty($nome)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nome da cidade é obrigatório."]);
            exit();
        }
        try {
            if ($id > 0) {
                $query = "UPDATE cidades SET nome = :nome WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':nome' => $nome, ':id' => $id]);
                echo json_encode(["status" => "success", "message" => "Cidade atualizada com sucesso!"]);
            } else {
                $query = "INSERT INTO cidades (nome) VALUES (:nome)";
                $stmt = $db->prepare($query);
                $stmt->execute([':nome' => $nome]);
                echo json_encode(["status" => "success", "message" => "Cidade adicionada com sucesso!"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao salvar cidade: " . $e->getMessage()]);
        }
        break;

    case 'delete_cidade':
        $id = isset($data->id) ? (int)$data->id : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID inválido."]);
            exit();
        }
        try {
            $query = "DELETE FROM cidades WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            echo json_encode(["status" => "success", "message" => "Cidade removida com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao remover cidade: " . $e->getMessage()]);
        }
        break;

    // Especialidades CRUD
    case 'save_especialidade':
        $nome = isset($data->nome) ? trim($data->nome) : '';
        $id = isset($data->id) ? (int)$data->id : 0;
        if (empty($nome)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nome da especialidade é obrigatório."]);
            exit();
        }
        try {
            if ($id > 0) {
                $query = "UPDATE especialidades SET nome = :nome WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':nome' => $nome, ':id' => $id]);
                echo json_encode(["status" => "success", "message" => "Especialidade atualizada com sucesso!"]);
            } else {
                $query = "INSERT INTO especialidades (nome) VALUES (:nome)";
                $stmt = $db->prepare($query);
                $stmt->execute([':nome' => $nome]);
                echo json_encode(["status" => "success", "message" => "Especialidade adicionada com sucesso!"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao salvar especialidade: " . $e->getMessage()]);
        }
        break;

    case 'delete_especialidade':
        $id = isset($data->id) ? (int)$data->id : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID inválido."]);
            exit();
        }
        try {
            $query = "DELETE FROM especialidades WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            echo json_encode(["status" => "success", "message" => "Especialidade removida com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao remover especialidade: " . $e->getMessage()]);
        }
        break;

    // Convenios CRUD
    case 'save_convenio':
        $nome = isset($data->nome) ? trim($data->nome) : '';
        $id = isset($data->id) ? (int)$data->id : 0;
        if (empty($nome)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nome do convênio é obrigatório."]);
            exit();
        }
        try {
            if ($id > 0) {
                $query = "UPDATE convenios SET nome_convenio = :nome WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([':nome' => $nome, ':id' => $id]);
                echo json_encode(["status" => "success", "message" => "Convênio atualizado com sucesso!"]);
            } else {
                $query = "INSERT INTO convenios (nome_convenio) VALUES (:nome)";
                $stmt = $db->prepare($query);
                $stmt->execute([':nome' => $nome]);
                echo json_encode(["status" => "success", "message" => "Convênio adicionado com sucesso!"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao salvar convênio: " . $e->getMessage()]);
        }
        break;

    case 'delete_convenio':
        $id = isset($data->id) ? (int)$data->id : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID inválido."]);
            exit();
        }
        try {
            $query = "DELETE FROM convenios WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            echo json_encode(["status" => "success", "message" => "Convênio removido com sucesso!"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Erro ao remover convênio: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Ação inválida."]);
        break;
}

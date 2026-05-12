<?php
require_once __DIR__ . '/../config/app_config.php';
require_once __DIR__ . '/../config/cors.php';

include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

$email = isset($data->email) ? trim($data->email) : "";
$senha = isset($data->senha) ? $data->senha : "";

if (!empty($email) && !empty($senha)) {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Formato de e-mail inválido."]);
        exit();
    }

    $usuario->email = $email;
    $stmt = $usuario->findByEmail();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($senha, $row['senha'])) {

            $payload = [
                "id" => $row['id'],
                "email" => $email,
                "tipo" => $row['tipo'],
                "exp" => time() + JWT_EXPIRACAO
            ];

            $tokenPayload = base64_encode(json_encode($payload));
            $assinatura = hash_hmac('sha256', $tokenPayload, JWT_SECRET);
            $tokenFinal = $tokenPayload . "." . $assinatura;

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login realizado!",
                "token" => $tokenFinal,
                "tipo" => $row['tipo'],
                "nome" => $row['nome'] ?? null
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Senha incorreta."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Usuário não encontrado."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Preencha todos os campos"]);
}

<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/app_config.php';


include_once '../config/database.php';
include_once '../models/Usuario.php';

// Protege contra saída acidental (warnings/whitespace/BOM) que podem corromper JSON
if (!ob_get_level()) {
    ob_start();
}

if (!function_exists('send_json')) {
    function send_json($data, $code = 200)
    {
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code($code);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit();
    }
}

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

$email = isset($data->email) ? trim($data->email) : "";
$senha = isset($data->senha) ? $data->senha : "";

if (!empty($email) && !empty($senha)) {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_json(["status" => "error", "message" => "Formato de e-mail inválido."], 400);
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
            send_json([
                "status" => "success",
                "message" => "Login realizado!",
                "token" => $tokenFinal,
                "tipo" => $row['tipo'],
                "nome" => $row['nome'] ?? null
            ], 200);
        } else {
            send_json(["status" => "error", "message" => "Senha incorreta."], 401);
        }
    } else {
        send_json(["status" => "error", "message" => "Usuário não encontrado."], 404);
    }
} else {
    send_json(["status" => "error", "message" => "Preencha todos os campos"], 400);
}

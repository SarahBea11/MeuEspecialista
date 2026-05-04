<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Max-Age: 86400");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->senha)) {

    $usuario->email = $data->email;
    $stmt = $usuario->findByEmail();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($data->senha, $row['senha'])) {

            $payload = [
                "id" => $row['id'],
                "email" => $data->email,
                "tipo" => $row['tipo'],
                "exp" => time() + (60 * 60 * 8)
            ];

            $tokenPayload = base64_encode(json_encode($payload));

            $chave_secreta = "MINHA_CHAVE_SUPER_SECRETA_123";
            $assinatura = hash_hmac('sha256', $tokenPayload, $chave_secreta);

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
 
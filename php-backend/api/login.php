<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");
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
echo json_encode($data);
exit();
    if ($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($data->senha, $row['senha'])) {

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login realizado!",
                "tipo" => $row['tipo']
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


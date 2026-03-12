<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->senha)) {
    $usuario->email = $data->email;
    $stmt = $usuario->findByEmail();
    $num = $stmt->rowCount();

    if($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($data->senha == $row['senha']) {
            http_response_code(200);
            echo json_encode(array(
                "status" => "success",
                "message" => "Login realizado!",
                "tipo" => $row['tipo']
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("status" => "error", "message" => "Senha incorreta."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("status" => "error", "message" => "Usuário não encontrado."));
    }
}
?>
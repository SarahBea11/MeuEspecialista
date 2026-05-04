<?php
if (!function_exists('getallheaders')) {
    function getallheaders() 
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function verificarAutenticacao()
{
    $headers = getallheaders();

    $chave_secreta = "MINHA_CHAVE_SUPER_SECRETA_123";

    $authHeader = null;
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    }

    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Token não fornecido."]);
        exit();
    }

    $token = str_replace("Bearer ", "", $authHeader);

    $partes = explode('.', $token);
    if (count($partes) !== 2) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Formato de token inválido."]);
        exit();
    }

    $payloadBase64 = $partes[0];
    $assinaturaEnviada = $partes[1];

    $assinaturaEsperada = hash_hmac('sha256', $payloadBase64, $chave_secreta);

    if ($assinaturaEnviada !== $assinaturaEsperada) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Acesso negado. Token violado."]);
        exit();
    }

    $decodedPayload = base64_decode($payloadBase64);
    $dados = json_decode($decodedPayload);

    if (!$dados) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Dados do token corrompidos."]);
        exit();
    }

    if (isset($dados->exp) && $dados->exp < time()) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Sessão expirada."]);
        exit();
    }

    return $dados;
}

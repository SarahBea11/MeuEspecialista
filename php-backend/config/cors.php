<?php
// Configuração centralizada de CORS

// Permitir o frontend Angular (localhost:4200)
$allowed_origin = "http://localhost:4200";

// Iniciar buffer de saída para evitar que qualquer saída acidental quebre respostas JSON
if (!function_exists('sanitize_api_output')) {
    function sanitize_api_output($buffer)
    {
        // remover BOM se presente
        $buffer = preg_replace('/^\xEF\xBB\xBF/', '', $buffer);
        // procurar primeiro objeto/array JSON
        $posBrace = strpos($buffer, '{');
        $posBracket = strpos($buffer, '[');
        if ($posBrace === false && $posBracket === false) {
            return $buffer;
        }
        if ($posBrace === false) $pos = $posBracket;
        else if ($posBracket === false) $pos = $posBrace;
        else $pos = min($posBrace, $posBracket);
        return substr($buffer, $pos);
    }
}

if (!ob_get_level()) {
    ob_start('sanitize_api_output');
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

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: $allowed_origin");
}

header("Access-Control-Max-Age: 86400");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept");
header("Content-Type: application/json; charset=UTF-8");

// Lidar com a requisição de pré-vôo (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    http_response_code(200);
    exit(0);
}


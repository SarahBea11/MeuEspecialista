<?php
// Configuração centralizada de CORS
// Em produção, altere o valor abaixo para o domínio real do seu frontend.

$origin = "http://localhost:4200";

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $currentOrigin = $_SERVER['HTTP_ORIGIN'];
    // Se quiser permitir múltiplos domínios, você pode validar $currentOrigin aqui.
    if ($currentOrigin === $origin) {
        header("Access-Control-Allow-Origin: $currentOrigin");
        header("Access-Control-Allow-Credentials: true");
    }
} else {
    header("Access-Control-Allow-Origin: $origin");
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 86400");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

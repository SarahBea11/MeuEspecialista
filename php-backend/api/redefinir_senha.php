<?php
/**
 * Endpoint: redefinir_senha.php
 * Valida o token e atualiza a senha do usuário.
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$token        = trim($dados['token'] ?? '');
$nova_senha   = $dados['nova_senha'] ?? '';
$confirmar    = $dados['confirmar_senha'] ?? '';

// Validações básicas
if (empty($token) || empty($nova_senha) || empty($confirmar)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Preencha todos os campos."]);
    exit();
}

if ($nova_senha !== $confirmar) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "As senhas não coincidem."]);
    exit();
}

if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $nova_senha)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "A senha deve ter pelo menos 8 caracteres, incluir letras, números e símbolo."]);
    exit();
}

$database = new Database();
$pdo = $database->getConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Falha na conexão com o banco de dados."]);
    exit();
}

// Buscar token válido e não expirado
$stmt = $pdo->prepare("
    SELECT usuario_id FROM password_reset_tokens
    WHERE token = ? AND expiracao > NOW()
");
$stmt->execute([$token]);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resultado) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Token inválido ou expirado. Solicite um novo link."]);
    exit();
}

$usuario_id = $resultado['usuario_id'];

// Atualizar a senha com hash seguro
$hash = password_hash($nova_senha, PASSWORD_BCRYPT);
$pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")->execute([$hash, $usuario_id]);

// Invalidar o token (usar apenas uma vez)
$pdo->prepare("DELETE FROM password_reset_tokens WHERE usuario_id = ?")->execute([$usuario_id]);

echo json_encode(["status" => "success", "message" => "Senha redefinida com sucesso! Faça login com sua nova senha."]);

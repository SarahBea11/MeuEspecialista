<?php
/**
 * Endpoint: verificar_favorito.php
 *
 * GET → Verifica se o paciente logado favoritou um médico específico.
 *       Query param: medico_usuario_id
 *
 * Resposta:
 *   { "favoritado": true/false, "notificacoes_ativas": true/false }
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_middleware.php';

header('Content-Type: application/json; charset=utf-8');

$usuarioLogado = verificarAutenticacao();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit();
}

// Médicos não têm favoritos — retorna sempre false
if ($usuarioLogado->tipo !== 'paciente') {
    echo json_encode(['favoritado' => false, 'notificacoes_ativas' => false]);
    exit();
}

$medicoUsuarioId = intval($_GET['medico_usuario_id'] ?? 0);

if ($medicoUsuarioId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do médico inválido.']);
    exit();
}

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Falha na conexão com o banco de dados.']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT notificacoes_ativas
        FROM favoritos
        WHERE paciente_usuario_id = ? AND medico_usuario_id = ?
    ");
    $stmt->execute([$usuarioLogado->id, $medicoUsuarioId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'favoritado'          => true,
            'notificacoes_ativas' => (bool)$row['notificacoes_ativas'],
        ]);
    } else {
        echo json_encode([
            'favoritado'          => false,
            'notificacoes_ativas' => false,
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao verificar favorito.']);
}

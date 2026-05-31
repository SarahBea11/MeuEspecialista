<?php
/**
 * Endpoint: favoritar_medico.php
 *
 * POST  → Toggle favorito (adiciona se não existe, remove se já existe)
 *         Body: { "medico_usuario_id": 5 }
 *
 * PUT   → Altera preferência de notificações de um favorito existente
 *         Body: { "medico_usuario_id": 5, "notificacoes_ativas": 0 }
 *
 * Apenas pacientes autenticados podem usar este endpoint.
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_middleware.php';

header('Content-Type: application/json; charset=utf-8');

$usuarioLogado = verificarAutenticacao();

// Somente pacientes podem favoritar
if ($usuarioLogado->tipo !== 'paciente') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Apenas pacientes podem favoritar médicos.']);
    exit();
}

$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Falha na conexão com o banco de dados.']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$dados = json_decode(file_get_contents('php://input'), true);

$medicoUsuarioId = intval($dados['medico_usuario_id'] ?? 0);

if ($medicoUsuarioId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do médico inválido.']);
    exit();
}

// Verificar se o médico existe e é realmente um médico
$stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND tipo = 'medico'");
$stmtCheck->execute([$medicoUsuarioId]);
if (!$stmtCheck->fetch()) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Médico não encontrado.']);
    exit();
}

$pacienteId = $usuarioLogado->id;

if ($method === 'POST') {
    // Verificar se já é favorito
    $stmtExiste = $pdo->prepare("SELECT id FROM favoritos WHERE paciente_usuario_id = ? AND medico_usuario_id = ?");
    $stmtExiste->execute([$pacienteId, $medicoUsuarioId]);
    $favorito = $stmtExiste->fetch(PDO::FETCH_ASSOC);

    if ($favorito) {
        // Já existe → remover (toggle off)
        $stmtDel = $pdo->prepare("DELETE FROM favoritos WHERE paciente_usuario_id = ? AND medico_usuario_id = ?");
        $stmtDel->execute([$pacienteId, $medicoUsuarioId]);
        echo json_encode(['status' => 'success', 'favoritado' => false, 'message' => 'Médico removido dos favoritos.']);
    } else {
        // Não existe → adicionar (toggle on)
        $stmtIns = $pdo->prepare("INSERT INTO favoritos (paciente_usuario_id, medico_usuario_id, notificacoes_ativas) VALUES (?, ?, 1)");
        $stmtIns->execute([$pacienteId, $medicoUsuarioId]);
        echo json_encode(['status' => 'success', 'favoritado' => true, 'notificacoes_ativas' => true, 'message' => 'Médico adicionado aos favoritos!']);
    }

} elseif ($method === 'PUT') {
    // Alterar preferência de notificações
    $notificacoes = isset($dados['notificacoes_ativas']) ? intval($dados['notificacoes_ativas']) : 1;

    $stmtUpd = $pdo->prepare("UPDATE favoritos SET notificacoes_ativas = ? WHERE paciente_usuario_id = ? AND medico_usuario_id = ?");
    $stmtUpd->execute([$notificacoes, $pacienteId, $medicoUsuarioId]);

    if ($stmtUpd->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Favorito não encontrado.']);
    } else {
        echo json_encode(['status' => 'success', 'notificacoes_ativas' => (bool)$notificacoes, 'message' => 'Preferência de notificações atualizada.']);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
}

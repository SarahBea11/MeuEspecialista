<?php
/**
 * Endpoint: listar_favoritos.php
 *
 * GET → Retorna todos os médicos favoritados pelo paciente logado,
 *       incluindo informações do perfil médico e status de notificação.
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

if ($usuarioLogado->tipo !== 'paciente') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Apenas pacientes podem acessar favoritos.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
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
        SELECT
            u.id           AS medico_usuario_id,
            u.nome,
            u.email,
            m.crm,
            m.especialidade,
            m.cidade,
            m.telefone,
            m.endereco,
            m.foto,
            m.atualizado_em,
            f.notificacoes_ativas,
            f.criado_em    AS favoritado_em
        FROM favoritos f
        INNER JOIN usuarios u       ON u.id = f.medico_usuario_id
        INNER JOIN medicos_perfil m ON m.usuario_id = f.medico_usuario_id
        WHERE f.paciente_usuario_id = ?
        ORDER BY f.criado_em DESC
    ");

    $stmt->execute([$usuarioLogado->id]);
    $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Converter flags para boolean
    foreach ($favoritos as &$fav) {
        $fav['notificacoes_ativas'] = (bool)$fav['notificacoes_ativas'];
    }

    echo json_encode($favoritos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao listar favoritos.']);
}

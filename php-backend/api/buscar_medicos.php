<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';

include_once '../config/database.php';
include_once '../config/auth_middleware.php';
include_once '../config/security_helpers.php';

header('Content-Type: application/json; charset=utf-8');

$usuarioLogado = verificarAutenticacao();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Falha na conexão com o banco de dados.',
    ]);
    exit();
}

$cidade = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$especialidade = isset($_GET['especialidade']) ? trim($_GET['especialidade']) : '';
$termo = isset($_GET['termo']) ? trim($_GET['termo']) : '';

$paciente_id = ($usuarioLogado && isset($usuarioLogado->tipo) && $usuarioLogado->tipo === 'paciente') ? (int)$usuarioLogado->id : 0;

$query = "SELECT u.id AS id, u.nome, u.email, m.especialidade, m.cidade, m.telefone, m.crm, m.endereco, m.foto, m.atualizado_em,
                 (CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END) AS favoritado,
                 COALESCE(f.notificacoes_ativas, 0) AS notificacoes_ativas
          FROM usuarios u
          INNER JOIN medicos_perfil m ON u.id = m.usuario_id
          LEFT JOIN favoritos f ON f.medico_usuario_id = u.id AND f.paciente_usuario_id = :paciente_id
          WHERE u.tipo = 'medico'";

if (!empty($cidade)) {
    $query .= " AND m.cidade LIKE :cidade";
}

if (!empty($especialidade)) {
    $query .= " AND m.especialidade LIKE :especialidade";
}

// Note: we remove term query from database side because CRM is encrypted.
// We will filter in PHP.

try {
    $stmt = $db->prepare($query);
    $stmt->bindParam(":paciente_id", $paciente_id, PDO::PARAM_INT);

    if (!empty($cidade)) {
        $cidadeParam = "%{$cidade}%";
        $stmt->bindParam(":cidade", $cidadeParam);
    }

    if (!empty($especialidade)) {
        $especialidadeParam = "%{$especialidade}%";
        $stmt->bindParam(":especialidade", $especialidadeParam);
    }

    // Note: termo binding removed from statement prepare since we filter in PHP.

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    foreach ($rows as $row) {
        $row['crm'] = decryptData($row['crm'], true);
        $row['telefone'] = decryptData($row['telefone'], false);
        
        if (!empty($termo)) {
            $nomeMatch = (stripos($row['nome'], $termo) !== false);
            $crmMatch = (stripos($row['crm'], $termo) !== false);
            if ($nomeMatch || $crmMatch) {
                $result[] = $row;
            }
        } else {
            $result[] = $row;
        }
    }
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao executar a busca de médicos.',
        'details' => $e->getMessage(),
    ]);
    exit();
}

<?php
require_once __DIR__ . '/../config/cors.php';

include_once '../config/database.php';
include_once '../config/auth_middleware.php';

$usuarioLogado = verificarAutenticacao();

$database = new Database();
$db = $database->getConnection();

$cidade = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$especialidade = isset($_GET['especialidade']) ? trim($_GET['especialidade']) : '';
$termo = isset($_GET['termo']) ? trim($_GET['termo']) : '';

$query = "SELECT u.nome, m.especialidade, m.cidade, m.telefone, m.crm
          FROM usuarios u
          INNER JOIN medicos_perfil m ON u.id = m.usuario_id
          WHERE u.tipo = 'medico'";

if (!empty($cidade)) {
    $query .= " AND m.cidade LIKE :cidade";
}

if (!empty($especialidade)) {
    $query .= " AND m.especialidade LIKE :especialidade";
}

if (!empty($termo)) {
    $query .= " AND (u.nome LIKE :termo OR m.crm LIKE :termo)";
}

$stmt = $db->prepare($query);

if (!empty($cidade)) {
    $cidadeParam = "%{$cidade}%";
    $stmt->bindParam(":cidade", $cidadeParam);
}

if (!empty($especialidade)) {
    $especialidadeParam = "%{$especialidade}%";
    $stmt->bindParam(":especialidade", $especialidadeParam);
}

if (!empty($termo)) {
    $termoParam = "%{$termo}%";
    $stmt->bindParam(":termo", $termoParam);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);

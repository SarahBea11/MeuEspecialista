<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$cidade = isset($_GET['cidade']) ? $_GET['cidade'] : '';
$especialidade = isset($_GET['especialidade']) ? $_GET['especialidade'] : '';

$query = "SELECT u.nome, m.especialidade, m.cidade, m.telefone
          FROM usuarios u
          INNER JOIN medicos_perfil m ON u.id = m.usuario_id
          WHERE u.tipo = 'medico'";

if (!empty($cidade)) {
    $query .= " AND m.cidade = :cidade";
}

if (!empty($especialidade)) {
    $query .= " AND m.especialidade = :especialidade";
}

$stmt = $db->prepare($query);

if (!empty($cidade)) {
    $stmt->bindParam(":cidade", $cidade);
}

if (!empty($especialidade)) {
    $stmt->bindParam(":especialidade", $especialidade);
}

$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
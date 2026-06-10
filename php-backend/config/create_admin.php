<?php
include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Erro de conexão.\n");
}

$nome = "Administrador";
$email = "admin@especialista.com";
$senha = "Admin@123";
$tipo = "admin";

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Verifica se já existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() > 0) {
        // Atualiza a senha e garante o tipo admin
        $stmtUpdate = $db->prepare("UPDATE usuarios SET senha = :senha, tipo = 'admin' WHERE email = :email");
        $stmtUpdate->execute([':senha' => $senhaHash, ':email' => $email]);
        echo "Administrador existente atualizado com sucesso!\n";
    } else {
        // Insere novo admin
        $stmtInsert = $db->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
        $stmtInsert->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash,
            ':tipo' => $tipo
        ]);
        echo "Novo administrador criado com sucesso!\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

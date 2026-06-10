<?php
require_once __DIR__ . '/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        die("Erro ao conectar ao banco de dados.\n");
    }

    // 1. Modificar coluna tipo em usuarios para suportar admin
    echo "Alterando a coluna tipo da tabela usuarios...\n";
    $db->exec("ALTER TABLE usuarios MODIFY COLUMN tipo ENUM('paciente', 'medico', 'admin') NOT NULL");

    // 2. Criar tabela cidades
    echo "Criando tabela cidades...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `cidades` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nome` varchar(100) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `nome` (`nome`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 3. Criar tabela especialidades
    echo "Criando tabela especialidades...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS `especialidades` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nome` varchar(100) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `nome` (`nome`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // 4. Inserir valores padrão de cidades
    echo "Populando cidades...\n";
    $cidades = ['Campinas', 'Indaiatuba', 'Itu'];
    $stmt = $db->prepare("INSERT IGNORE INTO cidades (nome) VALUES (:nome)");
    foreach ($cidades as $c) {
        $stmt->execute([':nome' => $c]);
    }

    // 5. Inserir valores padrão de especialidades
    echo "Populando especialidades...\n";
    $especialidades = ['Cardiologia', 'Pediatria', 'Psiquiatria'];
    $stmt = $db->prepare("INSERT IGNORE INTO especialidades (nome) VALUES (:nome)");
    foreach ($especialidades as $e) {
        $stmt->execute([':nome' => $e]);
    }

    echo "Banco de dados atualizado com sucesso!\n";
} catch (Exception $e) {
    die("Erro ao atualizar banco: " . $e->getMessage() . "\n");
}

<?php
include_once __DIR__ . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Check if column already exists
    $result = $db->query("SHOW COLUMNS FROM medicos_perfil LIKE 'foto'");
    if ($result->rowCount() === 0) {
        $db->exec("ALTER TABLE medicos_perfil ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
        echo "SUCCESS: Column 'foto' added successfully!\n";
    } else {
        echo "INFO: Column 'foto' already exists!\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

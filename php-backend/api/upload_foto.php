<?php
require_once __DIR__ . '/../config/cors.php';
include_once '../config/database.php';
include_once '../config/auth_middleware.php';

// Verifica a autenticação do token JWT
$usuarioLogado = verificarAutenticacao();

if ($usuarioLogado->tipo !== 'medico') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Apenas médicos podem fazer upload de foto."]);
    exit;
}

if (!isset($_FILES['foto'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nenhuma imagem foi recebida."]);
    exit;
}

$file = $_FILES['foto'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array(strtolower($file['type']), $allowedTypes)) {
    // Fallback: check extension if mimetype check fails in some configurations
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Tipo de arquivo inválido. Apenas JPG, PNG ou WEBP são permitidos."]);
        exit;
    }
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "O tamanho da imagem não deve exceder 5MB."]);
    exit;
}

// Gera um nome único de arquivo
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = "medico_" . $usuarioLogado->id . "_" . time() . "." . $ext;
$uploadsDir = __DIR__ . '/../uploads/';

// Cria o diretório de uploads se não existir
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$destPath = $uploadsDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $destPath)) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Exclui a foto antiga se ela existir para economizar espaço
        $stmtGetOld = $db->prepare("SELECT foto FROM medicos_perfil WHERE usuario_id = :id");
        $stmtGetOld->execute([':id' => $usuarioLogado->id]);
        $oldPhoto = $stmtGetOld->fetchColumn();
        if ($oldPhoto && file_exists($uploadsDir . $oldPhoto)) {
            unlink($uploadsDir . $oldPhoto);
        }
        
        // Atualiza a coluna no banco de dados
        $query = "UPDATE medicos_perfil SET foto = :foto WHERE usuario_id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':foto' => $fileName,
            ':id' => $usuarioLogado->id
        ]);
        
        echo json_encode([
            "status" => "success",
            "message" => "Foto atualizada com sucesso!",
            "foto" => $fileName
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erro ao salvar no banco de dados: " . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Falha ao salvar o arquivo enviado."]);
}

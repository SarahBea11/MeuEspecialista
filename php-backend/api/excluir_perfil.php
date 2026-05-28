<?php
require_once __DIR__ . '/../config/cors.php';
include_once '../config/database.php';
include_once '../config/auth_middleware.php';

$usuarioLogado = verificarAutenticacao();

$idUsuario = $usuarioLogado->id;
$tipoUsuario = $usuarioLogado->tipo;

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();

    // Se for médico, removemos a foto de perfil do disco antes de deletar do banco
    if ($tipoUsuario === 'medico') {
        $stmtGetOld = $db->prepare("SELECT foto FROM medicos_perfil WHERE usuario_id = :id");
        $stmtGetOld->execute([':id' => $idUsuario]);
        $oldPhoto = $stmtGetOld->fetchColumn();
        
        if ($oldPhoto) {
            $uploadsDir = __DIR__ . '/../uploads/';
            if (file_exists($uploadsDir . $oldPhoto)) {
                unlink($uploadsDir . $oldPhoto);
            }
        }
    }

    // Ao deletar o usuário da tabela `usuarios`, os registros correspondentes em 
    // `medicos_perfil` ou `pacientes_perfil` (e consequentemente `medico_convenio`)
    // serão removidos automaticamente por causa da restrição ON DELETE CASCADE.
    $query = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $idUsuario]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Usuário não encontrado.");
    }

    $db->commit();
    echo json_encode([
        "status" => "success",
        "message" => "Perfil excluído com sucesso!"
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Erro ao excluir perfil: " . $e->getMessage()
    ]);
}

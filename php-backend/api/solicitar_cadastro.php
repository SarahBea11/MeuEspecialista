<?php
ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/email_config.php';
include_once '../config/database.php';
include_once '../config/auth_middleware.php';

// Carregar PHPMailer
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$usuarioLogado = verificarAutenticacao();

if (!isset($usuarioLogado->tipo) || $usuarioLogado->tipo !== 'medico') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acesso permitido apenas para médicos."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

$tipo_solicitacao = isset($data->tipo) ? trim($data->tipo) : '';
$descricao = isset($data->descricao) ? trim($data->descricao) : '';

if (empty($tipo_solicitacao) || empty($descricao)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Tipo e descrição são obrigatórios."]);
    exit();
}

// Buscar nome do médico
$stmtMed = $db->prepare("SELECT nome, email FROM usuarios WHERE id = :id");
$stmtMed->execute([':id' => $usuarioLogado->id]);
$medico = $stmtMed->fetch(PDO::FETCH_ASSOC);
$medicoNome = $medico['nome'] ?? 'Médico';
$medicoEmail = $medico['email'] ?? '';

// Buscar o e-mail de todos os administradores
$stmtAdmins = $db->prepare("SELECT email, nome FROM usuarios WHERE tipo = 'admin'");
$stmtAdmins->execute();
$admins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

if (empty($admins)) {
    // Fallback: enviar para o e-mail padrão do sistema
    $admins = [['email' => EMAIL_USERNAME, 'nome' => 'Administrador']];
}

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = EMAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USERNAME;
    $mail->Password   = EMAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = EMAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(EMAIL_USERNAME, 'MeuEspecialista — Sistema');

    foreach ($admins as $admin) {
        $mail->addAddress($admin['email'], $admin['nome']);
    }

    // Para fins de desenvolvimento/teste, também envia para o e-mail do SMTP (desenvolvedor)
    if (defined('EMAIL_USERNAME') && !empty(EMAIL_USERNAME)) {
        $alreadyAdded = false;
        foreach ($admins as $admin) {
            if (strcasecmp($admin['email'], EMAIL_USERNAME) === 0) {
                $alreadyAdded = true;
                break;
            }
        }
        if (!$alreadyAdded) {
            $mail->addAddress(EMAIL_USERNAME, 'Administrador (Cópia de Desenvolvimento)');
        }
    }

    // Responder ao médico
    if (!empty($medicoEmail)) {
        $mail->addReplyTo($medicoEmail, $medicoNome);
    }

    $mail->isHTML(true);
    $mail->Subject = "📋 Nova Solicitação: {$tipo_solicitacao} — MeuEspecialista";
    $mail->Body    = "
    <div style='font-family: Inter, sans-serif; max-width: 600px; margin: auto; padding: 32px; background: #f4f7f6; border-radius: 16px;'>
        <h2 style='color: #044F23; margin-bottom: 4px;'>📋 Nova Solicitação de Cadastro</h2>
        <p style='color: #666; margin-top: 0;'>Via plataforma MeuEspecialista</p>
        
        <div style='background: white; border-radius: 12px; padding: 24px; margin: 24px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);'>
            <p style='margin: 0 0 12px;'><strong>Médico Solicitante:</strong> {$medicoNome}</p>
            <p style='margin: 0 0 12px;'><strong>E-mail do médico:</strong> {$medicoEmail}</p>
            <p style='margin: 0 0 12px;'><strong>Tipo de Solicitação:</strong> {$tipo_solicitacao}</p>
            <div style='border-top: 1px solid #f0f0f0; margin: 16px 0; padding-top: 16px;'>
                <strong>Descrição / Detalhes:</strong>
                <p style='color: #333; background: #f9f9f9; padding: 14px; border-radius: 8px; margin-top: 8px; border-left: 4px solid #044F23;'>{$descricao}</p>
            </div>
        </div>

        <p style='color: #888; font-size: 13px;'>Acesse o painel administrativo para aprovar e cadastrar este novo registro.</p>
    </div>
    ";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "Solicitação enviada com sucesso! Nossa equipe analisará em breve."
    ]);
} catch (MailException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro ao enviar a solicitação. Tente novamente mais tarde."]);
}

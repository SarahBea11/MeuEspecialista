<?php
require_once __DIR__ . '/../config/email_config.php';

// Carregar PHPMailer (fallback para libs se vendor/autoload não existir)
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

// Args: tipo, descricao, medicoNome, medicoEmail
$tipo = $argv[1] ?? 'Cadastro de Especialidade';
$descricao = $argv[2] ?? 'Descrição de teste: favor validar cadastro.';
$medicoNome = $argv[3] ?? 'Dr. Teste';
$medicoEmail = $argv[4] ?? 'medico.teste@example.com';

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

    $mail->setFrom(EMAIL_USERNAME, 'MeuEspecialista — Sistema (TEST)');

    // Envia para o administrador de fallback (EMAIL_USERNAME)
    $mail->addAddress(EMAIL_USERNAME, 'Administrador (fallback)');

    // Responder ao médico
    if (!empty($medicoEmail)) {
        $mail->addReplyTo($medicoEmail, $medicoNome);
    }

    $mail->isHTML(true);
    $mail->Subject = "[TEST] Nova Solicitação: {$tipo} — MeuEspecialista";
    $mail->Body    = "
    <div style='font-family: Inter, sans-serif; max-width: 600px; margin: auto; padding: 32px; background: #f4f7f6; border-radius: 16px;'>
        <h2 style='color: #044F23; margin-bottom: 4px;'>📋 Nova Solicitação de Cadastro (TEST)</h2>
        <p style='color: #666; margin-top: 0;'>Via script de teste</p>
        <div style='background: white; border-radius: 12px; padding: 24px; margin: 24px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);'>
            <p style='margin: 0 0 12px;'><strong>Médico Solicitante:</strong> {$medicoNome}</p>
            <p style='margin: 0 0 12px;'><strong>E-mail do médico:</strong> {$medicoEmail}</p>
            <p style='margin: 0 0 12px;'><strong>Tipo de Solicitação:</strong> {$tipo}</p>
            <div style='border-top: 1px solid #f0f0f0; margin: 16px 0; padding-top: 16px;'>
                <strong>Descrição / Detalhes:</strong>
                <p style='color: #333; background: #f9f9f9; padding: 14px; border-radius: 8px; margin-top: 8px; border-left: 4px solid #044F23;'>{$descricao}</p>
            </div>
        </div>
        <p style='color: #888; font-size: 13px;'>Este é um e-mail de teste. Ignore se não aplicável.</p>
    </div>
    ";

    $mail->send();

    echo "OK: E-mail de solicitação de teste enviado para " . EMAIL_USERNAME . "\n";
} catch (MailException $e) {
    echo "ERROR: Falha ao enviar e-mail de teste. Mensagem: " . $e->getMessage() . "\n";
}

echo "Args usados:\n";
echo "  tipo: $tipo\n";
echo "  descricao: $descricao\n";
echo "  medicoNome: $medicoNome\n";
echo "  medicoEmail: $medicoEmail\n";

?>
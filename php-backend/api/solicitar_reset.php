<?php
/**
 * Endpoint: solicitar_reset.php
 * Recebe o e-mail, gera um token e envia o link por e-mail usando PHPMailer + Gmail.
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email_config.php';

// Carregar PHPMailer ANTES dos `use` statements
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/../libs/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$email = trim($dados['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "E-mail inválido."]);
    exit();
}

$database = new Database();
$pdo = $database->getConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Falha na conexão com o banco de dados."]);
    exit();
}

// Verificar se o e-mail existe na base
$stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Resposta genérica para não revelar se o e-mail existe (segurança)
if (!$usuario) {
    echo json_encode(["status" => "success", "message" => "Se o e-mail existir, você receberá as instruções."]);
    exit();
}

// Gerar token seguro e definir expiração (1 hora)
$token = bin2hex(random_bytes(32));
$expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Salvar token na tabela (deletar tokens antigos do mesmo usuário primeiro)
$pdo->prepare("DELETE FROM password_reset_tokens WHERE usuario_id = ?")->execute([$usuario['id']]);
$stmt = $pdo->prepare("INSERT INTO password_reset_tokens (usuario_id, token, expiracao) VALUES (?, ?, ?)");
$stmt->execute([$usuario['id'], $token, $expiracao]);

// Montar link de redefinição
$link = "http://localhost:4200/redefinir-senha?token=" . $token;

// Enviar e-mail via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = EMAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USERNAME;
    $mail->Password   = EMAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = EMAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(EMAIL_USERNAME, 'MeuEspecialista');
    $mail->addAddress($email, $usuario['nome']);
    $mail->isHTML(true);
    $mail->Subject = 'Redefinição de Senha — MeuEspecialista';
    $mail->Body    = "
    <div style='font-family: Poppins, sans-serif; max-width: 500px; margin: auto; padding: 32px; background: #f4f7f6; border-radius: 12px;'>
        <h2 style='color: #044F23;'>🩺 MeuEspecialista</h2>
        <p>Olá, <strong>{$usuario['nome']}</strong>!</p>
        <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
        <p>Clique no botão abaixo para criar uma nova senha. O link expira em <strong>1 hora</strong>.</p>
        <a href='{$link}' style='display:inline-block; margin: 24px 0; padding: 14px 28px; background: #044F23; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>
            Redefinir minha senha
        </a>
        <p style='color: #888; font-size: 13px;'>Se você não solicitou isso, ignore este e-mail. Sua senha permanece a mesma.</p>
    </div>
    ";

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Se o e-mail existir, você receberá as instruções."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro ao enviar e-mail. Verifique as configurações SMTP."]);
}

<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/email_config.php';

include_once '../config/database.php';
include_once '../config/auth_middleware.php';
include_once '../models/Usuario.php';

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

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Dados não recebidos."]);
    exit;
}

$nome = isset($data->nome) ? trim($data->nome) : "";
$email = isset($data->email) ? trim($data->email) : "";
$idUsuario = $usuarioLogado->id;

if (empty($nome) || empty($email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nome e E-mail são obrigatórios."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Formato de e-mail inválido."]);
    exit;
}

$usuario->email = $email;
if ($usuario->emailExisteParaOutroUsuario($idUsuario)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Este e-mail já está em uso por outro usuário."]);
    exit;
}

try {
    $db->beginTransaction();

    $queryUser = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':id' => $idUsuario
    ]);

    if ($usuarioLogado->tipo === 'medico') {
        $queryExtra = "UPDATE medicos_perfil SET 
                        crm = :crm, 
                        especialidade = :especialidade, 
                        cidade = :cidade, 
                        telefone = :telefone, 
                        endereco = :endereco 
                       WHERE usuario_id = :id";
        $stmtExtra = $db->prepare($queryExtra);
        $stmtExtra->execute([
            ':crm' => $data->crm,
            ':especialidade' => $data->especialidade,
            ':cidade' => $data->cidade,
            ':telefone' => $data->telefone,
            ':endereco' => $data->endereco,
            ':id' => $idUsuario
        ]);
    } else {
        $queryExtra = "UPDATE pacientes_perfil SET 
                        cpf = :cpf, 
                        cidade = :cidade, 
                        telefone = :telefone, 
                        endereco = :endereco 
                       WHERE usuario_id = :id";
        $stmtExtra = $db->prepare($queryExtra);
        $stmtExtra->execute([
            ':cpf' => $data->cpf,
            ':cidade' => $data->cidade,
            ':telefone' => $data->telefone,
            ':endereco' => $data->endereco,
            ':id' => $idUsuario
        ]);
    }

    if (!empty($data->senha)) {
        if (strlen($data->senha) < 6) {
            throw new Exception("A nova senha deve ter pelo menos 6 caracteres.");
        }
        if ($data->senha === ($data->confirmarSenha ?? "")) {
            $senhaHash = password_hash($data->senha, PASSWORD_DEFAULT);
            $querySenha = "UPDATE usuarios SET senha = :senha WHERE id = :id";
            $stmtSenha = $db->prepare($querySenha);
            $stmtSenha->execute([':senha' => $senhaHash, ':id' => $idUsuario]);
        } else {
            throw new Exception("As senhas não coincidem.");
        }
    }

    $db->commit();

    // ── Notificações: disparar e-mails para pacientes que favoritaram este médico ──
    if ($usuarioLogado->tipo === 'medico') {
        try {
            // Buscar nome do médico atualizado
            $stmtMedNome = $db->prepare("SELECT nome FROM usuarios WHERE id = ?");
            $stmtMedNome->execute([$idUsuario]);
            $medNome = $stmtMedNome->fetchColumn() ?: 'Um médico';

            // Buscar pacientes com notificações ativas
            $stmtPacientes = $db->prepare("
                SELECT u.email, u.nome, u.id AS paciente_id
                FROM favoritos f
                INNER JOIN usuarios u ON u.id = f.paciente_usuario_id
                WHERE f.medico_usuario_id = ? AND f.notificacoes_ativas = 1
            ");
            $stmtPacientes->execute([$idUsuario]);
            $pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pacientes as $paciente) {
                // Registrar notificação no banco
                $mensagem = "O Dr(a). {$medNome} atualizou o perfil. Acesse o MeuEspecialista para ver as informações mais recentes.";
                $stmtNot = $db->prepare("INSERT INTO notificacoes (paciente_usuario_id, medico_usuario_id, mensagem) VALUES (?, ?, ?)");
                $stmtNot->execute([$paciente['paciente_id'], $idUsuario, $mensagem]);

                // Enviar e-mail
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

                    $mail->setFrom(EMAIL_USERNAME, 'MeuEspecialista');
                    $mail->addAddress($paciente['email'], $paciente['nome']);
                    $mail->isHTML(true);
                    $mail->Subject = "🔔 {$medNome} atualizou o perfil — MeuEspecialista";
                    $mail->Body    = "
                    <div style='font-family: Poppins, sans-serif; max-width: 520px; margin: auto; padding: 32px; background: #f4f7f6; border-radius: 12px;'>
                        <h2 style='color: #044F23;'>🩺 MeuEspecialista</h2>
                        <p>Olá, <strong>{$paciente['nome']}</strong>!</p>
                        <p>Um médico que você favoritou acabou de atualizar as informações do perfil:</p>
                        <div style='background: white; border-left: 4px solid #044F23; padding: 16px 20px; border-radius: 8px; margin: 20px 0;'>
                            <p style='margin:0; font-size: 16px; font-weight: 700; color: #044F23;'>🩺 {$medNome}</p>
                        </div>
                        <p>Acesse o MeuEspecialista para conferir as informações atualizadas, como telefone, endereço, especialidade e convênios.</p>
                        <a href='http://localhost:4200/buscar' style='display:inline-block; margin: 24px 0; padding: 14px 28px; background: #044F23; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>
                            Ver perfil atualizado
                        </a>
                        <p style='color: #888; font-size: 12px;'>Para parar de receber notificações deste médico, acesse seus Favoritos e desative as notificações.</p>
                    </div>
                    ";
                    $mail->send();
                } catch (MailException $mailEx) {
                    // Falha no e-mail não deve impedir a resposta de sucesso
                    error_log('Erro ao enviar e-mail de notificação: ' . $mailEx->getMessage());
                }
            }
        } catch (Exception $notifEx) {
            error_log('Erro ao processar notificações: ' . $notifEx->getMessage());
        }
    }
    // ── Fim das notificações ──

    echo json_encode(["status" => "success", "message" => "Perfil atualizado com sucesso!"]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
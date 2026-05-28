<?php
/**
 * Configurações de e-mail para envio via Gmail SMTP (Exemplo).
 *
 * Copie este arquivo e renomeie para 'email_config.php' no mesmo diretório
 * antes de preencher com suas credenciais reais.
 *
 * IMPORTANTE: Para Gmail, você precisa gerar uma "Senha de App":
 * 1. Acesse https://myaccount.google.com/apppasswords
 * 2. Ative a Verificação em 2 etapas na conta de e-mail.
 * 3. Crie uma nova Senha de App (escolha "Outro" e digite "MeuEspecialista").
 * 4. Use a senha gerada (16 caracteres) em EMAIL_PASSWORD abaixo.
 */

define('EMAIL_HOST',     'smtp.gmail.com');
define('EMAIL_PORT',     587);
define('EMAIL_USERNAME', 'seu_email@gmail.com');   // ← Troque pelo seu Gmail
define('EMAIL_PASSWORD', 'sua_senha_de_app_aqui'); // ← Senha de App do Gmail (16 chars, sem espaços)

<?php
/**
 * Configurações de e-mail para envio via Gmail SMTP.
 *
 * IMPORTANTE: Para Gmail, você precisa gerar uma "Senha de App":
 * 1. Acesse myaccount.google.com
 * 2. Segurança → Verificação em 2 etapas (ative se necessário)
 * 3. Segurança → Senhas de app → Gerar senha para "Correio"
 * 4. Use a senha gerada (16 caracteres) em EMAIL_PASSWORD abaixo.
 */

define('EMAIL_HOST',     'smtp.gmail.com');
define('EMAIL_PORT',     587);
define('EMAIL_USERNAME', 'matheusnathan320@gmail.com');   // ← Troque pelo seu Gmail
define('EMAIL_PASSWORD', 'gritmrmcdmclcbom'); // ← Senha de App do Gmail (16 chars)

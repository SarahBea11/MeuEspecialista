<?php

/**
 * Arquivo de configuração — EXEMPLO
 *
 * Copie este arquivo para app_config.php e preencha com os seus valores:
 *
 *   cp app_config.example.php app_config.php
 *
 * O arquivo app_config.php está no .gitignore e NÃO deve ser commitado.
 */

// Chave usada para assinar e verificar os tokens de autenticação.
// Gere uma chave forte e aleatória. Exemplo (Linux/Mac):
//   openssl rand -hex 64
define('JWT_SECRET', 'COLOQUE_AQUI_SUA_CHAVE_SECRETA');

// Tempo de expiração do token em segundos (padrão: 8 horas)
define('JWT_EXPIRACAO', 60 * 60 * 8);

// Chave usada para criptografar dados sensíveis (CPF, CRM, telefone).
// Deve conter 32 bytes (64 caracteres hexadecimais). Gere com:
//   openssl rand -hex 32
// E então converta para binário usando hex2bin():
define('ENCRYPTION_KEY', hex2bin('COLOQUE_AQUI_32_BYTES_EM_HEXADECIMAL_EXEMPLO_0011223344556677889900aabbccddeeff'));

// Método de criptografia (deve ser suportado pelo OpenSSL)
define('ENCRYPTION_METHOD', 'AES-256-CBC');

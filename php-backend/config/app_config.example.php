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

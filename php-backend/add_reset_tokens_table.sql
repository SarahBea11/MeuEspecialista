-- Execute este script no phpMyAdmin para criar a tabela de tokens de recuperação de senha.
-- Acesse: http://localhost/phpmyadmin → Selecione "meu_especialista" → Aba SQL → Cole e execute.

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expiracao` DATETIME NOT NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_reset_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

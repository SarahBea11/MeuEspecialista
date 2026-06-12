-- ============================================================
-- MeuEspecialista — Estrutura do banco de dados
-- Atualizado em: 2026-06-10
-- Importar em: phpMyAdmin > Selecionar banco > Aba SQL > Executar
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. usuarios (sem dependências)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('paciente','medico','admin') NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 2. cidades (sem dependências)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `cidades`;
CREATE TABLE `cidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cidades` (`nome`) VALUES
  ('Campinas'),
  ('Indaiatuba'),
  ('Itu');

-- ------------------------------------------------------------
-- 3. especialidades (sem dependências)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `especialidades`;
CREATE TABLE `especialidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `especialidades` (`nome`) VALUES
  ('Alergologia'),
  ('Anestesiologia'),
  ('Angiologia'),
  ('Cardiologia'),
  ('Cirurgia Geral'),
  ('Cirurgia Plástica'),
  ('Coloproctologia'),
  ('Dermatologia'),
  ('Endocrinologia'),
  ('Fisiatria'),
  ('Gastroenterologia'),
  ('Geriatria'),
  ('Ginecologia e Obstetrícia'),
  ('Hematologia'),
  ('Infectologia'),
  ('Mastologia'),
  ('Medicina do Trabalho'),
  ('Nefrologia'),
  ('Neurologia'),
  ('Nutrologia'),
  ('Oftalmologia'),
  ('Oncologia'),
  ('Ortopedia'),
  ('Otorrinolaringologia'),
  ('Pediatria'),
  ('Pneumologia'),
  ('Psiquiatria'),
  ('Reumatologia'),
  ('Urologia');

-- ------------------------------------------------------------
-- 4. convenios (sem dependências)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `convenios`;
CREATE TABLE `convenios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_convenio` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome_convenio` (`nome_convenio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `convenios` (`nome_convenio`) VALUES
  ('Allianz Saúde'),
  ('Amil'),
  ('Bradesco Saúde'),
  ('Care Plus'),
  ('Golden Cross'),
  ('GreenLine'),
  ('Intermédica (GNDI)'),
  ('NotreDame Intermédica'),
  ('Porto Seguro Saúde'),
  ('Sompo Saúde'),
  ('SulAmérica Saúde'),
  ('Unimed');

-- ------------------------------------------------------------
-- 3. medicos_perfil (depende de: usuarios)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `medicos_perfil`;
CREATE TABLE `medicos_perfil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `crm` varchar(255) NOT NULL,
  `especialidade` varchar(100) NOT NULL,
  `telefone` varchar(255) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `endereco` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `crm` (`crm`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `medicos_perfil_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 4. pacientes_perfil (depende de: usuarios, convenios)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `pacientes_perfil`;
CREATE TABLE `pacientes_perfil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `cpf` varchar(255) NOT NULL,
  `convenio_id` int(11) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  KEY `usuario_id` (`usuario_id`),
  KEY `convenio_id` (`convenio_id`),
  CONSTRAINT `pacientes_perfil_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pacientes_perfil_ibfk_2` FOREIGN KEY (`convenio_id`) REFERENCES `convenios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 5. medico_convenio (depende de: medicos_perfil, convenios)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `medico_convenio`;
CREATE TABLE `medico_convenio` (
  `medico_id` int(11) NOT NULL,
  `convenio_id` int(11) NOT NULL,
  PRIMARY KEY (`medico_id`, `convenio_id`),
  KEY `convenio_id` (`convenio_id`),
  CONSTRAINT `medico_convenio_ibfk_1` FOREIGN KEY (`medico_id`) REFERENCES `medicos_perfil` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medico_convenio_ibfk_2` FOREIGN KEY (`convenio_id`) REFERENCES `convenios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 6. password_reset_tokens (depende de: usuarios)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracao` datetime NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 7. favoritos (depende de: usuarios)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_usuario_id` int(11) NOT NULL,
  `medico_usuario_id` int(11) NOT NULL,
  `notificacoes_ativas` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorito` (`paciente_usuario_id`,`medico_usuario_id`),
  KEY `medico_usuario_id` (`medico_usuario_id`),
  CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`paciente_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`medico_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 8. notificacoes (depende de: usuarios)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `notificacoes`;
CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_usuario_id` int(11) NOT NULL,
  `medico_usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `enviado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `lido` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `paciente_usuario_id` (`paciente_usuario_id`),
  KEY `medico_usuario_id` (`medico_usuario_id`),
  CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`paciente_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notificacoes_ibfk_2` FOREIGN KEY (`medico_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

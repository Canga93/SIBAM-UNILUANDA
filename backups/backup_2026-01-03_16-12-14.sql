CREATE TABLE `galerias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text,
  `imagem` varchar(255) NOT NULL,
  `data_publicacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `time` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO login_attempts VALUES ('1', NULL, '1756556270', '::1', '2025-08-30 02:17:50');

CREATE TABLE `monografias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `resumo` text NOT NULL,
  `autor_id` int NOT NULL,
  `orientador` varchar(100) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `palavras_chave` varchar(255) DEFAULT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_publicacao` date DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_submissao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `autor_id` (`autor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO monografias VALUES ('1', 'Capacidade cognitiva', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '2', 'Adriano Dumbo', 'Gestão', 'Gestão, pensamento, cognitiva, capacidade, pensar, raciocinar', '1767452974_roteamento_inter_vlan cisco_e_huawei.pdf', '2020-06-23', 'aprovado', '2026-01-03 16:09:34'),
('2', 'Gestão Integral', 'Gestão Integral é uma filosofia de gestão que considera todas as dimensões de uma organização de forma interconectada, não apenas as partes isoladas.', '1', 'hhhhh', 'Gestao', 'hhhhh,ghjjkk', '1767455023_DECLARAÇÃO  EDITADA.pdf', '2026-01-06', 'aprovado', '2026-01-03 16:43:43'),
('3', 'Sistema de Braço Robótico Inteligente para Agricultura de Precisão com Visão Computacional', 'Automatização da colheita seletiva de frutas e vegetais em ambientes agrícolas, com capacidade de identificar o estado de maturação, tamanho e presença de defeitos, reduzindo desperdícios e aumentando a eficiência da colheita.', '4', 'Neide', 'Engenharia Mecatronica', 'automação, eficiencia, desperdicios', '1767456505_02_09_2025 07_14_13 - MATRÍCULAS DO ALUNO.pdf', '2025-09-24', 'rejeitado', '2026-01-03 17:08:25');

CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`(250)),
  KEY `idx_email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO password_resets VALUES ('1', 'admin@uniluanda.edu.ao', 'ed919ae04313aea5146d09fe15daaa3e8a7df7f8d1674304bcf251d37218751fbed29fabec9ecd035f61ada08c80b96d3604', '2025-08-30 12:02:46', '2025-08-30 01:02:46');

CREATE TABLE `scheduled_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `scheduled_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assigned_to` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `support_tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('aberto','em_andamento','resolvido','fechado') DEFAULT 'aberto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO system_logs VALUES ('1', '1', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 00:51:48'),
('2', '2', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 01:07:58'),
('3', '1', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 01:08:44'),
('4', NULL, 'login_failed', 'Tentativa de login falhou: admin@uniluanda.edu.ao', '::1', '2025-08-30 02:17:50'),
('5', '1', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 02:17:59'),
('6', '2', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 10:03:58'),
('7', '1', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 11:18:23'),
('8', '1', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 11:33:07'),
('9', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 09:42:22'),
('10', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 09:45:52'),
('11', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 09:57:08'),
('12', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 10:03:01'),
('13', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 16:08:07'),
('14', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 16:10:59'),
('15', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 16:57:06'),
('16', NULL, 'login_failed', 'Tentativa de login falhou: joao.silva@uniluanda.edu.ao', '::1', '2026-01-03 17:05:50'),
('17', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 17:08:44');

CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('admin','estudante','professor') DEFAULT 'estudante',
  `foto` varchar(255) DEFAULT 'default.png',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO usuarios VALUES ('1', 'Administrador', 'admin@uniluanda.edu.ao', '$2y$10$hm9VN5SNxmhBqN17aqfqk.E9VUMVU7jnxuUtZ1B2bXL6aHqrGDr6i', 'admin', 'shine.jpg', '2025-08-28 03:44:17', '2025-08-28 05:27:11'),
('2', 'Bela Lando', 'bela@uniluanda.edu.ao', '$2y$10$ogyOTUHYVcBYT4HYav80OONE9VE0cnW9nP0bJggFwXQ5QJGtLL78S', 'estudante', 'default.png', '2025-08-28 04:54:02', '2025-08-30 01:09:04'),
('3', 'João Pedro', 'joao@uniluanda.edu.ao', '$2y$10$jinSkr6WaPAvLFeRhkt3jOMiukYAiXN8jqUWS2nWU4fxQJUUnQyuK', 'professor', 'default.png', '2025-08-30 11:29:01', '2025-08-30 11:29:01'),
('4', 'André Carlos', 'andrecarlos@uniluanda.edu.ao', '$2y$10$H2cHHmqEKL/SuZQOQB7r5OwO7ZVgJqgj9fFn1IPiJ4TYi4TG/JKR.', 'estudante', 'default.png', '2026-01-03 17:06:15', '2026-01-03 17:06:15');


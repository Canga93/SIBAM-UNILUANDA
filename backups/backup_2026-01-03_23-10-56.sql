CREATE TABLE `categorias_galeria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `cor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#6f42c1',
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias_galeria VALUES ('1', 'Projetos Acadêmicos', 'Projetos desenvolvidos em disciplinas', '#3498db', 'ativo', '2026-01-03 21:45:14'),
('2', 'Trabalhos de Conclusão', 'Monografias e TCCs', '#2ecc71', 'ativo', '2026-01-03 21:45:14'),
('3', 'Eventos Universitários', 'Fotos de eventos da universidade', '#e74c3c', 'ativo', '2026-01-03 21:45:14'),
('4', 'Infraestrutura', 'Estrutura física da universidade', '#f39c12', 'ativo', '2026-01-03 21:45:14'),
('5', 'Equipe Docente', 'Professores e coordenadores', '#9b59b6', 'ativo', '2026-01-03 21:45:14'),
('6', 'Laboratórios', 'Equipamentos e laboratórios', '#1abc9c', 'ativo', '2026-01-03 21:45:14'),
('7', 'Cerimônias', 'Colações de grau e formaturas', '#d35400', 'ativo', '2026-01-03 21:45:14'),
('8', 'Atividades Extracurriculares', 'Workshops, seminários e palestras', '#34495e', 'ativo', '2026-01-03 21:45:14'),
('9', 'Visitantes Ilustres', 'Personalidades que visitaram a universidade', '#8e44ad', 'ativo', '2026-01-03 21:45:14'),
('10', 'Parcerias', 'Empresas e instituições parceiras', '#16a085', 'ativo', '2026-01-03 21:45:14');

CREATE TABLE `galerias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int DEFAULT NULL,
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `usuario_id` int DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `visualizacoes` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_data` (`data_criacao`),
  CONSTRAINT `galerias_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_galeria` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO galerias VALUES ('1', 'Cerimonia de outorga', 'Gestão Logística', '69598a0974d45_1767475721.webp', '7', 'ativo', '1', '2026-01-03 22:28:41', '0'),
('2', 'Docentes IPGEST', 'DDDFGJK', '69598df8d05a8_1767476728.jpeg', '5', 'ativo', '1', '2026-01-03 22:45:28', '0'),
('3', 'Bibioteca Ipgest', 'rrrrryyyyyyyyyyuuuuuuuu', '69598e69bcc51_1767476841.jpg', '4', 'ativo', '1', '2026-01-03 22:47:21', '0'),
('4', 'Sala de aula pós Graduação', 'ttttttttttttttttttttttttttttttiiiiiiiiiiiiiii', '69598ed4977cb_1767476948.jpg', '4', 'ativo', '1', '2026-01-03 22:49:08', '0'),
('5', 'Ilustração Frontal IPGEST', 'VVVVVVVVVVVVVVVVMMMMMMMMMMMMMM', '6959913c25b5a_1767477564.png', '4', 'ativo', '1', '2026-01-03 22:59:24', '0'),
('6', 'Engenheiro Desenvolvedor do SIBAM-UNILUANDA', 'Desenvolvedor', '695991a083268_1767477664.jpg', '10', 'ativo', '1', '2026-01-03 23:01:04', '0');

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

CREATE TABLE `logs_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `acao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_data` (`data_hora`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO logs_admin VALUES ('1', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:28:42', NULL, NULL),
('2', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:45:28', NULL, NULL),
('3', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:47:21', NULL, NULL),
('4', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:49:08', NULL, NULL),
('5', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:59:24', NULL, NULL),
('6', '1', 'Upload de 1 imagens na galeria', '2026-01-03 23:01:04', NULL, NULL),
('7', '1', 'Alterou status da imagem ID 5 para inativo', '2026-01-03 23:04:26', NULL, NULL),
('8', '1', 'Alterou status da imagem ID 5 para ativo', '2026-01-03 23:04:55', NULL, NULL),
('9', '1', 'Alterou status da imagem ID 5 para inativo', '2026-01-03 23:07:40', NULL, NULL),
('10', '1', 'Alterou status da imagem ID 5 para ativo', '2026-01-03 23:08:32', NULL, NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
('17', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 17:08:44'),
('18', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 17:12:45'),
('19', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 17:24:18'),
('20', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 23:21:24');

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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO usuarios VALUES ('1', 'Administrador', 'admin@uniluanda.edu.ao', '$2y$10$hm9VN5SNxmhBqN17aqfqk.E9VUMVU7jnxuUtZ1B2bXL6aHqrGDr6i', 'admin', 'shine.jpg', '2025-08-28 03:44:17', '2025-08-28 05:27:11'),
('2', 'Bela Lando', 'bela@uniluanda.edu.ao', '$2y$10$ogyOTUHYVcBYT4HYav80OONE9VE0cnW9nP0bJggFwXQ5QJGtLL78S', 'estudante', 'default.png', '2025-08-28 04:54:02', '2025-08-30 01:09:04'),
('3', 'João Pedro', 'joao@uniluanda.edu.ao', '$2y$10$jinSkr6WaPAvLFeRhkt3jOMiukYAiXN8jqUWS2nWU4fxQJUUnQyuK', 'estudante', 'default.png', '2025-08-30 11:29:01', '2026-01-03 23:22:03'),
('4', 'André Carlos', 'andrecarlos@uniluanda.edu.ao', '$2y$10$H2cHHmqEKL/SuZQOQB7r5OwO7ZVgJqgj9fFn1IPiJ4TYi4TG/JKR.', 'estudante', 'default.png', '2026-01-03 17:06:15', '2026-01-03 17:06:15'),
('5', 'Ivandro José', 'ivandro@uniluanda.edu.ao', '$2y$10$yvHiSPXZ5nQKTy75PNxWRO9z0VVLKBvljwI33dGQCy6x2E1ucidp2', 'estudante', 'default.png', '2026-01-03 23:30:27', '2026-01-03 23:30:27'),
('6', 'Joana João', 'Joana@uniluanda.edu.ao', '$2y$10$uoOTHZmmXeLkZcOZZ7ECS.xM1IhNaUPy/UtV7w/uA67LWWS/BVd5q', 'estudante', 'default.png', '2026-01-04 00:09:21', '2026-01-04 00:09:21');


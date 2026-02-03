CREATE TABLE `categorias_galeria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#6f42c1',
  `status` enum('ativo','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
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
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int DEFAULT NULL,
  `status` enum('ativo','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `usuario_id` int DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `visualizacoes` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_data` (`data_criacao`),
  KEY `fk_galerias_usuario` (`usuario_id`),
  CONSTRAINT `fk_galerias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `galerias_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_galeria` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO galerias VALUES ('1', 'Cerimonia de outorga', 'Gestão Logística', '69598a0974d45_1767475721.webp', '7', 'ativo', '1', '2026-01-03 22:28:41', '0'),
('2', 'Docentes IPGEST', 'DDDFGJK', '69598df8d05a8_1767476728.jpeg', '5', 'ativo', '1', '2026-01-03 22:45:28', '0'),
('3', 'Bibioteca Ipgest', 'rrrrryyyyyyyyyyuuuuuuuu', '69598e69bcc51_1767476841.jpg', '4', 'ativo', '1', '2026-01-03 22:47:21', '0'),
('4', 'Sala de aula pós Graduação', 'ttttttttttttttttttttttttttttttiiiiiiiiiiiiiii', '69598ed4977cb_1767476948.jpg', '4', 'inativo', '1', '2026-01-03 22:49:08', '0'),
('5', 'Ilustração Frontal IPGEST', 'VVVVVVVVVVVVVVVVMMMMMMMMMMMMMM', '6959913c25b5a_1767477564.png', '4', 'inativo', '1', '2026-01-03 22:59:24', '0'),
('6', 'Engenheiro Desenvolvedor do SIBAM-UNILUANDA', 'Desenvolvedor', '695991a083268_1767477664.jpg', '10', 'ativo', '1', '2026-01-03 23:01:04', '0');

CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `time` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_login_attempts_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO login_attempts VALUES ('1', NULL, '1756556270', '::1', '2025-08-30 02:17:50'),
('2', NULL, '1767520337', '::1', '2026-01-04 10:52:17');

CREATE TABLE `logs_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `acao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_data` (`data_hora`),
  KEY `fk_logs_admin_usuario` (`admin_id`),
  CONSTRAINT `fk_logs_admin_usuario` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO logs_admin VALUES ('1', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:28:42', NULL, NULL),
('2', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:45:28', NULL, NULL),
('3', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:47:21', NULL, NULL),
('4', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:49:08', NULL, NULL),
('5', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:59:24', NULL, NULL),
('6', '1', 'Upload de 1 imagens na galeria', '2026-01-03 23:01:04', NULL, NULL),
('7', '1', 'Alterou status da imagem ID 5 para inativo', '2026-01-03 23:04:26', NULL, NULL),
('8', '1', 'Alterou status da imagem ID 5 para ativo', '2026-01-03 23:04:55', NULL, NULL),
('9', '1', 'Alterou status da imagem ID 5 para inativo', '2026-01-03 23:07:40', NULL, NULL),
('10', '1', 'Alterou status da imagem ID 5 para ativo', '2026-01-03 23:08:32', NULL, NULL),
('11', '1', 'Alterou status da imagem ID 5 para inativo', '2026-01-04 10:57:50', NULL, NULL),
('12', '1', 'Alterou status da imagem ID 5 para ativo', '2026-01-04 10:58:02', NULL, NULL),
('13', '1', 'Alterou status da imagem ID 5 para inativo', '2026-01-18 15:22:22', NULL, NULL),
('14', '1', 'Alterou status da imagem ID 4 para inativo', '2026-01-18 15:22:59', NULL, NULL);

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
  KEY `autor_id` (`autor_id`),
  CONSTRAINT `fk_monografias_autor` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO monografias VALUES ('1', 'Capacidade cognitiva', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '2', 'Adriano Dumbo', 'Gestão', 'Gestão, pensamento, cognitiva, capacidade, pensar, raciocinar', '1767452974_roteamento_inter_vlan cisco_e_huawei.pdf', '2020-06-23', 'aprovado', '2026-01-03 16:09:34'),
('3', 'Sistema de Braço Robótico Inteligente para Agricultura de Precisão com Visão Computacional', 'Automatização da colheita seletiva de frutas e vegetais em ambientes agrícolas, com capacidade de identificar o estado de maturação, tamanho e presença de defeitos, reduzindo desperdícios e aumentando a eficiência da colheita.', '4', 'Neide', 'Engenharia Mecatronica', 'automação, eficiencia, desperdicios', '1767456505_02_09_2025 07_14_13 - MATRÍCULAS DO ALUNO.pdf', '2025-09-24', 'rejeitado', '2026-01-03 17:08:25'),
('4', 'Tecnologias Digirais', 'Tecnologias digitais, tem estado presente no dia a dia dos profissionais', '8', 'Paulo', 'Engenharia Informatica', 'Digital, tecnologia, sistemas, plataformas', '1767518374_apostila-excel-avancado.pdf', '2024-12-17', 'aprovado', '2026-01-04 10:19:35'),
('5', 'Mobilidade urbana', 'É a forma como as pessoas e mercadorias se deslocam dentro das cidades, envolvendo transportes e infraestrutura', '7', 'Mambo', 'Engenharia dos Transportes', 'urbano, transporte, cidades, pessoas, mercadorias', '1767519392_02_09_2025 07_14_13 - MATRÍCULAS DO ALUNO.pdf', '2025-10-22', 'aprovado', '2026-01-04 10:36:32'),
('6', 'Gestão operacional de aeroportos', 'Analisa os processos operacionais aeroportuários e estratégias para melhorar eficiência e segurança', '6', 'Matilde', 'Gestao Aeronautica', 'aeroportos, segurança, operação, processos', '1767520033_ap23.pdf', '2023-11-15', 'rejeitado', '2026-01-04 10:47:13'),
('7', 'Optimização da cadeia logística no transporte urbano', 'Alise de estratégias para reduzir custos e tempo de entrega em ambiente urbanos congestionado', '5', 'João', 'Gestao e Logistica', 'logística, sustentabilidade, estratégia, cadeia de abastecimento', '1767520306_Tradutor.pdf', '2025-04-09', 'pendente', '2026-01-04 10:51:46'),
('8', 'Sistemas embarcados', 'uwuieoirotppytyu+,mvnvbnvbvbvvvddsfggjkjkkkkk', '9', 'Juliana', 'Engenharia Informatica', 'sistemas, embarcados', '1768064414_cartaMãe.pdf', '2021-06-16', 'pendente', '2026-01-10 18:00:14');

CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_notifications_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`(250)),
  KEY `idx_email` (`email`),
  CONSTRAINT `fk_password_resets_email` FOREIGN KEY (`email`) REFERENCES `usuarios` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  KEY `user_id` (`user_id`),
  CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
('20', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-03 23:21:24'),
('21', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:09:31'),
('22', '8', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:15:48'),
('23', '7', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:20:00'),
('24', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:36:48'),
('25', '6', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:42:17'),
('26', '5', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:48:31'),
('27', NULL, 'login_failed', 'Tentativa de login falhou: admin@uniluanda.edu.ao', '::1', '2026-01-04 10:52:17'),
('28', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 10:52:48'),
('29', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 11:42:12'),
('30', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 11:48:57'),
('31', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-04 14:32:09'),
('32', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-06 16:34:38'),
('33', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-06 16:52:04'),
('34', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-09 13:08:43'),
('35', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-18 15:21:19');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO usuarios VALUES ('1', 'Administrador', 'admin@uniluanda.edu.ao', '$2y$10$hm9VN5SNxmhBqN17aqfqk.E9VUMVU7jnxuUtZ1B2bXL6aHqrGDr6i', 'admin', 'shine.jpg', '2025-08-28 03:44:17', '2025-08-28 05:27:11'),
('2', 'Bela Lando', 'bela@uniluanda.edu.ao', '$2y$10$ogyOTUHYVcBYT4HYav80OONE9VE0cnW9nP0bJggFwXQ5QJGtLL78S', 'estudante', 'default.png', '2025-08-28 04:54:02', '2025-08-30 01:09:04'),
('4', 'André Carlos', 'andrecarlos@uniluanda.edu.ao', '$2y$10$H2cHHmqEKL/SuZQOQB7r5OwO7ZVgJqgj9fFn1IPiJ4TYi4TG/JKR.', 'estudante', 'default.png', '2026-01-03 17:06:15', '2026-01-03 17:06:15'),
('5', 'Ivandro José', 'ivandro@uniluanda.edu.ao', '$2y$10$yvHiSPXZ5nQKTy75PNxWRO9z0VVLKBvljwI33dGQCy6x2E1ucidp2', 'estudante', 'default.png', '2026-01-03 23:30:27', '2026-01-03 23:30:27'),
('6', 'Joana João', 'Joana@uniluanda.edu.ao', '$2y$10$uoOTHZmmXeLkZcOZZ7ECS.xM1IhNaUPy/UtV7w/uA67LWWS/BVd5q', 'estudante', 'default.png', '2026-01-04 00:09:21', '2026-01-04 00:09:21'),
('7', 'Ana Maria', 'ana@uniluanda.edu.ao', '$2y$10$zji.KeZ9.Px1huHRGGOKoObb3bz6/.aOth6NpFTLgrApP4N8vml4q', 'estudante', 'default.png', '2026-01-04 10:13:23', '2026-01-04 10:13:23'),
('8', 'Lucrécia Paím', 'lucrecia@uniluanda.edu.ao', '$2y$10$jMopZHYy9G7CVJBymll/dOf5TTfzrjNVd.iaO5SxudRnhMYj8/4oe', 'estudante', 'default.png', '2026-01-04 10:14:41', '2026-01-04 11:49:51'),
('9', 'Anaíde Bango', 'anaide@uniluanda.edu.ao', '$2y$10$DF7/CEmft5zIEOGlbKF.y.UFjTjrlgD5Rstx1aYaknD.lr7diGW/y', 'estudante', 'default.png', '2026-01-10 17:57:53', '2026-01-18 15:23:40');


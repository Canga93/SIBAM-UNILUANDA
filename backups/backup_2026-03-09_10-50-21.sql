-- Backup gerado em 09/03/2026 10:50:21

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `categorias_galeria`;
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

INSERT INTO `categorias_galeria` VALUES 
('1', 'Projetos Acadêmicos', 'Projetos desenvolvidos em disciplinas', '#3498db', 'ativo', '2026-01-03 21:45:14'),
('2', 'Trabalhos de Conclusão', 'Monografias e TCCs', '#2ecc71', 'ativo', '2026-01-03 21:45:14'),
('3', 'Eventos Universitários', 'Fotos de eventos da universidade', '#e74c3c', 'ativo', '2026-01-03 21:45:14'),
('4', 'Infraestrutura', 'Estrutura física da universidade', '#f39c12', 'ativo', '2026-01-03 21:45:14'),
('5', 'Equipe Docente', 'Professores e coordenadores', '#9b59b6', 'ativo', '2026-01-03 21:45:14'),
('6', 'Laboratórios', 'Equipamentos e laboratórios', '#1abc9c', 'ativo', '2026-01-03 21:45:14'),
('7', 'Cerimônias', 'Colações de grau e formaturas', '#d35400', 'ativo', '2026-01-03 21:45:14'),
('8', 'Atividades Extracurriculares', 'Workshops, seminários e palestras', '#34495e', 'ativo', '2026-01-03 21:45:14'),
('9', 'Visitantes Ilustres', 'Personalidades que visitaram a universidade', '#8e44ad', 'ativo', '2026-01-03 21:45:14'),
('10', 'Parcerias', 'Empresas e instituições parceiras', '#16a085', 'ativo', '2026-01-03 21:45:14');

DROP TABLE IF EXISTS `conversas`;
CREATE TABLE `conversas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `assunto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_contato` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome_contato` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` datetime DEFAULT CURRENT_TIMESTAMP,
  `ultima_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `conversas` VALUES 
('1', NULL, NULL, 'Retificação da monografia', 'bela@uniluanda.edu.ao', 'Bela Lando', '2026-02-28 00:04:07', '2026-02-28 00:04:07');

DROP TABLE IF EXISTS `galerias`;
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

INSERT INTO `galerias` VALUES 
('1', 'Cerimonia de outorga', 'Gestão Logística', '69598a0974d45_1767475721.webp', '7', 'ativo', '1', '2026-01-03 22:28:41', '0'),
('2', 'Docentes IPGEST', 'DDDFGJK', '69598df8d05a8_1767476728.jpeg', '5', 'ativo', '1', '2026-01-03 22:45:28', '0'),
('3', 'Bibioteca Ipgest', 'rrrrryyyyyyyyyyuuuuuuuu', '69598e69bcc51_1767476841.jpg', '4', 'ativo', '1', '2026-01-03 22:47:21', '0'),
('4', 'Sala de aula pós Graduação', 'ttttttttttttttttttttttttttttttiiiiiiiiiiiiiii', '69598ed4977cb_1767476948.jpg', '4', 'inativo', '1', '2026-01-03 22:49:08', '0'),
('5', 'Ilustração Frontal IPGEST', 'VVVVVVVVVVVVVVVVMMMMMMMMMMMMMM', '6959913c25b5a_1767477564.png', '4', 'inativo', '1', '2026-01-03 22:59:24', '0'),
('6', 'Engenheiro Desenvolvedor do SIBAM-UNILUANDA', 'Desenvolvedor', '695991a083268_1767477664.jpg', '10', 'ativo', '1', '2026-01-03 23:01:04', '0');

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `time` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_login_attempts_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `login_attempts` VALUES 
('1', NULL, '1756556270', '::1', '2025-08-30 02:17:50'),
('2', NULL, '1767520337', '::1', '2026-01-04 10:52:17'),
('3', NULL, '1772233056', '::1', '2026-02-27 23:57:36');

DROP TABLE IF EXISTS `logs_admin`;
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

INSERT INTO `logs_admin` VALUES 
('1', '1', 'Upload de 1 imagens na galeria', '2026-01-03 22:28:42', NULL, NULL),
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

DROP TABLE IF EXISTS `mensagens`;
CREATE TABLE `mensagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `assunto` varchar(200) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `status` enum('lida','nao lida') DEFAULT 'nao lida',
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `mensagens` VALUES 
('2', 'André Carlos', 'andrecarlos@uniluanda.edu.ao', 'submissão', 'Pedido de submissão de monografias', 'nao lida', '2026-02-27 10:43:44', '::1'),
('3', 'Bela Lando', 'bela@uniluanda.edu.ao', 'Retificação da monografia', 'cccnvnmvkvkvvlvvioio', 'lida', '2026-02-27 11:18:11', '::1');

DROP TABLE IF EXISTS `mensagens_internas`;
CREATE TABLE `mensagens_internas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversa_id` int NOT NULL,
  `remetente_id` int NOT NULL,
  `destinatario_id` int DEFAULT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `lida` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `conversa_id` (`conversa_id`),
  KEY `remetente_id` (`remetente_id`),
  KEY `destinatario_id` (`destinatario_id`),
  KEY `lida` (`lida`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mensagens_internas` VALUES 
('1', '1', '1', NULL, 'vvvvvvvvvvv', '2026-02-28 00:04:07', '0'),
('2', '1', '1', NULL, 'bmmmmmmmmmmmmmvnnnnnnnn', '2026-02-28 00:04:24', '0'),
('3', '1', '1', NULL, 'vvvvvvvvvvv', '2026-02-28 00:04:38', '0'),
('4', '1', '1', NULL, 'cvbbbbbbbbbbbbbbbb', '2026-02-28 00:04:58', '0'),
('5', '1', '1', NULL, 'ertyuiopmnbvccxzxzzzz', '2026-03-01 23:50:49', '0');

DROP TABLE IF EXISTS `monografias`;
CREATE TABLE `monografias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `resumo` text NOT NULL,
  `autor_id` int NOT NULL,
  `orientador` varchar(100) DEFAULT NULL,
  `curso` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `palavras_chave` varchar(255) DEFAULT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_publicacao` date DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_submissao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `area` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `autor_id` (`autor_id`),
  KEY `idx_monografias_autor` (`autor_id`),
  KEY `idx_monografias_titulo` (`titulo`),
  KEY `idx_monografias_palavras_chave` (`palavras_chave`),
  FULLTEXT KEY `ft_titulo_palavras` (`titulo`,`palavras_chave`),
  FULLTEXT KEY `titulo` (`titulo`,`resumo`,`palavras_chave`),
  CONSTRAINT `fk_monografias_autor` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `monografias` VALUES 
('1', 'Capacidade cognitiva', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '2', 'Adriano Dumbo', 'Gestao e Logistica', 'Gestão, pensamento, cognitiva, capacidade, pensar, raciocinar', '1767452974_roteamento_inter_vlan cisco_e_huawei.pdf', '2020-06-23', 'aprovado', '2026-01-03 16:09:34', 'Gestao', NULL),
('4', 'Tecnologias Digirais', 'Tecnologias digitais, tem estado presente no dia a dia dos profissionais de TI', '8', 'Paulo', 'Engenharia Informatica', 'Digital, tecnologia, sistemas, plataformas', '1767518374_apostila-excel-avancado.pdf', '2024-12-17', 'aprovado', '2026-01-04 10:19:35', 'Engenharia', NULL),
('5', 'Mobilidade urbana', 'É a forma como as pessoas e mercadorias se deslocam dentro das cidades, envolvendo transportes e infraestrutura', '7', 'Mambo', 'Engenharia dos Transportes', 'urbano, transporte, cidades, pessoas, mercadorias', '1767519392_02_09_2025 07_14_13 - MATRÍCULAS DO ALUNO.pdf', '2025-10-22', 'aprovado', '2026-01-04 10:36:32', 'Engenharia', NULL),
('6', 'Gestão operacional de aeroportos', 'Analisa os processos operacionais aeroportuários e estratégias para melhorar eficiência e segurança', '6', 'Matilde', 'Gestao Aeronautica', 'aeroportos, segurança, operação, processos', '1767520033_ap23.pdf', '2023-11-15', 'rejeitado', '2026-01-04 10:47:13', 'Gestao', NULL),
('7', 'Optimização da cadeia logística no transporte urbano', 'Alise de estratégias para reduzir custos e tempo de entrega em ambiente urbanos congestionado', '5', 'João', 'Gestao e Logistica', 'logística, sustentabilidade, estratégia, cadeia de abastecimento', '1767520306_Tradutor.pdf', '2025-04-09', 'pendente', '2026-01-04 10:51:46', 'Gestao', NULL),
('8', 'Sistemas embarcados', 'uwuieoirotppytyu+,mvnvbnvbvbvvvddsfggjkjkkkkk', '9', 'Juliana', 'Engenharia Informatica', 'sistemas, embarcados', '1768064414_cartaMãe.pdf', '2021-06-16', 'pendente', '2026-01-10 18:00:14', 'Engenharia', NULL),
('9', 'Sistemas embarcados', 'DFGHHHHHHHHHHHHHHHHHKJ', '1', 'Julio', 'Informatica de Gestao', 'sistemas, embarcados', '1769193124_CV_Nsimba_Canga_Pedro_Profissional.pdf', '2020-10-16', 'aprovado', '2026-01-23 19:32:05', NULL, NULL),
('10', 'vivateste', 'vvvvvvvvvvvvvvvvvvvvistestsssssssssss', '1', 'Jovita', 'Gestao e Logistica', 'teste,viva', '1770767665_Profile.pdf', '2026-02-09', 'aprovado', '2026-02-11 00:54:25', 'Gestao', NULL);

DROP TABLE IF EXISTS `notifications`;
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

DROP TABLE IF EXISTS `password_resets`;
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

INSERT INTO `password_resets` VALUES 
('1', 'admin@uniluanda.edu.ao', 'ed919ae04313aea5146d09fe15daaa3e8a7df7f8d1674304bcf251d37218751fbed29fabec9ecd035f61ada08c80b96d3604', '2025-08-30 12:02:46', '2025-08-30 01:02:46');

DROP TABLE IF EXISTS `scheduled_tasks`;
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

DROP TABLE IF EXISTS `support_tickets`;
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

DROP TABLE IF EXISTS `system_logs`;
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
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `system_logs` VALUES 
('1', '1', 'login_success', 'Login realizado com sucesso', '::1', '2025-08-30 00:51:48'),
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
('35', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-18 15:21:19'),
('36', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-21 09:40:57'),
('37', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-21 09:53:56'),
('38', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-21 10:36:02'),
('39', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-23 19:26:15'),
('40', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-27 12:30:47'),
('41', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-27 12:31:38'),
('42', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-27 12:33:12'),
('43', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-29 22:16:12'),
('44', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-01-31 21:44:58'),
('45', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-01 23:37:19'),
('46', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-03 14:06:59'),
('47', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-03 14:09:08'),
('48', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-03 14:09:48'),
('49', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-03 14:32:16'),
('50', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-11 00:52:52'),
('51', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-18 14:26:26'),
('52', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-25 10:41:30'),
('53', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 11:18:40'),
('54', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 11:36:32'),
('55', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 12:01:09'),
('56', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 12:23:10'),
('57', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 12:23:44'),
('58', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 12:34:16'),
('59', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 12:40:47'),
('60', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 12:41:57'),
('61', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 13:44:03'),
('62', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 21:59:14'),
('63', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 22:19:11'),
('64', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 22:19:54'),
('65', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 22:34:31'),
('66', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 22:46:10'),
('67', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 22:59:36'),
('68', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 23:00:29'),
('69', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 23:13:26'),
('70', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 23:24:14'),
('71', NULL, 'login_failed', 'Tentativa de login falhou: admin@uniluanda.edu.ao', '::1', '2026-02-27 23:57:37'),
('72', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-27 23:57:46'),
('73', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-02-28 00:05:18'),
('74', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-03-01 12:37:38'),
('75', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-03-01 23:49:31'),
('76', '2', 'login_success', 'Login realizado com sucesso', '::1', '2026-03-01 23:53:16'),
('77', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-03-02 11:15:48'),
('78', '1', 'login_success', 'Login realizado com sucesso', '::1', '2026-03-09 11:35:10');

DROP TABLE IF EXISTS `usuarios`;
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
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `usuarios` VALUES 
('1', 'Administrador', 'admin@uniluanda.edu.ao', '$2y$10$hm9VN5SNxmhBqN17aqfqk.E9VUMVU7jnxuUtZ1B2bXL6aHqrGDr6i', 'admin', 'shine.jpg', '2025-08-28 03:44:17', '2025-08-28 05:27:11'),
('2', 'Bela Lando', 'bela@uniluanda.edu.ao', '$2y$10$ogyOTUHYVcBYT4HYav80OONE9VE0cnW9nP0bJggFwXQ5QJGtLL78S', 'estudante', 'default.png', '2025-08-28 04:54:02', '2026-01-27 12:33:40'),
('5', 'Ivandro José', 'ivandro@uniluanda.edu.ao', '$2y$10$yvHiSPXZ5nQKTy75PNxWRO9z0VVLKBvljwI33dGQCy6x2E1ucidp2', 'estudante', 'default.png', '2026-01-03 23:30:27', '2026-01-03 23:30:27'),
('6', 'Joana João', 'Joana@uniluanda.edu.ao', '$2y$10$uoOTHZmmXeLkZcOZZ7ECS.xM1IhNaUPy/UtV7w/uA67LWWS/BVd5q', 'estudante', 'default.png', '2026-01-04 00:09:21', '2026-01-04 00:09:21'),
('7', 'Ana Maria', 'ana@uniluanda.edu.ao', '$2y$10$zji.KeZ9.Px1huHRGGOKoObb3bz6/.aOth6NpFTLgrApP4N8vml4q', 'estudante', 'default.png', '2026-01-04 10:13:23', '2026-01-04 10:13:23'),
('8', 'Lucrécia Paím', 'lucrecia@uniluanda.edu.ao', '$2y$10$jMopZHYy9G7CVJBymll/dOf5TTfzrjNVd.iaO5SxudRnhMYj8/4oe', 'estudante', 'default.png', '2026-01-04 10:14:41', '2026-01-04 11:49:51'),
('9', 'Anaíde Bango', 'anaide@uniluanda.edu.ao', '$2y$10$DF7/CEmft5zIEOGlbKF.y.UFjTjrlgD5Rstx1aYaknD.lr7diGW/y', 'estudante', 'default.png', '2026-01-10 17:57:53', '2026-01-18 15:23:40'),
('10', 'Jorge Antunes', 'jorge@uniluanda.edu.ao', '$2y$10$9KBC5LpFVQ6sU/Af1D2rfOxg.zGJu0RG8MEuErm1bX.YqYI81jGrq', 'estudante', 'default.png', '2026-01-21 10:35:19', '2026-01-21 10:35:19'),
('11', 'Arminda Paulo', 'arminda@uniluanda.edu.ao', '$2y$10$w.KvFfJtWbtgi/nwLbw0a.m1vEq9DMwMy9DlLwzc6Tefu.cW50J3m', 'estudante', 'default.png', '2026-01-21 10:37:09', '2026-01-21 10:37:09'),
('12', 'Mariana Canga', 'mariana@uniluanda.edu.ao', '$2y$10$n6tWMyS5wd8SGLGlbeT3JekaIMW2SplF6mK0F1Z8tP3GZWjLbEvw6', 'estudante', 'default.png', '2026-01-21 10:40:23', '2026-01-21 10:40:23'),
('13', 'João Pedro', 'joao@uniluanda.edu.ao', '$2y$10$pC8NpVw656Ri2diHkyddiu7ceIZt52KIHV.BQEjoDb8fczmV6xfVC', 'estudante', 'default.png', '2026-01-21 10:41:21', '2026-01-21 10:41:21'),
('14', 'Pedro André', 'pedroandre@uniluanda.edu.ao', '$2y$10$fMxrbPREoM1INlXHPp4rS.grz0seqlGMGUaK27jxX5XqlZsjATR4e', 'estudante', 'default.png', '2026-01-23 19:34:31', '2026-02-25 10:42:03'),
('15', 'Mafuta Mbiavanga', 'mafutamviavanga@uniluanda.edu.ao', '$2y$10$ooMUzKE2T539tJSw4EFtruhKpKkC0AndeNy5vn0p8zmTCW/tU1fyi', 'estudante', 'default.png', '2026-03-09 11:38:02', '2026-03-09 11:38:02'),
('16', 'Pedro de Andrade', 'pedrodeandrade@uniluanda.edu.ao', '$2y$10$zno3IilbpwNqfnz5GuM4o.SZ0tegSQKPASHDer.kUj8rlZS/UqlXa', 'estudante', 'default.png', '2026-03-09 11:38:45', '2026-03-09 11:38:45'),
('17', 'João Silva', 'joao.silva@uniluanda.edu.ao', 'joao123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('18', 'Maria Santos', 'maria.santos@uniluanda.edu.ao', 'maria123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('19', 'José Ferreira', 'jose.ferreira@uniluanda.edu.ao', 'jose123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('20', 'António Costa', 'antonio.costa@uniluanda.edu.ao', 'antonio123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('21', 'Ana Oliveira', 'ana.oliveira@uniluanda.edu.ao', 'ana123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('22', 'Carlos Rodrigues', 'carlos.rodrigues@uniluanda.edu.ao', 'carlos123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('23', 'Fernanda Martins', 'fernanda.martins@uniluanda.edu.ao', 'fernanda123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('24', 'Pedro Fernandes', 'pedro.fernandes@uniluanda.edu.ao', 'pedro123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('25', 'Isabel Almeida', 'isabel.almeida@uniluanda.edu.ao', 'isabel123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('26', 'Luís Gomes', 'luis.gomes@uniluanda.edu.ao', 'luis123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('27', 'Sofia Lopes', 'sofia.lopes@uniluanda.edu.ao', 'sofia123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('28', 'Manuel Sousa', 'manuel.sousa@uniluanda.edu.ao', 'manuel123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('29', 'Rita Ribeiro', 'rita.ribeiro@uniluanda.edu.ao', 'rita123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('30', 'Paulo Carvalho', 'paulo.carvalho@uniluanda.edu.ao', 'paulo123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('31', 'Catarina Pinto', 'catarina.pinto@uniluanda.edu.ao', 'catarina123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('32', 'André Teixeira', 'andre.teixeira@uniluanda.edu.ao', 'andre123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('33', 'Teresa Correia', 'teresa.correia@uniluanda.edu.ao', 'teresa123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('34', 'Miguel Mendes', 'miguel.mendes@uniluanda.edu.ao', 'miguel123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('35', 'Inês Nunes', 'ines.nunes@uniluanda.edu.ao', 'ines123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('36', 'Ricardo Soares', 'ricardo.soares@uniluanda.edu.ao', 'ricardo123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('37', 'Patrícia Vieira', 'patricia.vieira@uniluanda.edu.ao', 'patricia123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('38', 'Diogo Monteiro', 'diogo.monteiro@uniluanda.edu.ao', 'diogo123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('39', 'Mariana Marques', 'mariana.marques@uniluanda.edu.ao', 'mariana123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('40', 'Bruno Barbosa', 'bruno.barbosa@uniluanda.edu.ao', 'bruno123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('41', 'Cláudia Araújo', 'claudia.araujo@uniluanda.edu.ao', 'claudia123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('42', 'Filipe Lima', 'filipe.lima@uniluanda.edu.ao', 'filipe123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('43', 'Daniela Cardoso', 'daniela.cardoso@uniluanda.edu.ao', 'daniela123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('44', 'Hugo Rocha', 'hugo.rocha@uniluanda.edu.ao', 'hugo123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('45', 'Sara Neves', 'sara.neves@uniluanda.edu.ao', 'sara123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('46', 'Nuno Cruz', 'nuno.cruz@uniluanda.edu.ao', 'nuno123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('47', 'Vanessa Cunha', 'vanessa.cunha@uniluanda.edu.ao', 'vanessa123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('48', 'Rui Pires', 'rui.pires@uniluanda.edu.ao', 'rui123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('49', 'Filipa Freitas', 'filipa.freitas@uniluanda.edu.ao', 'filipa123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('50', 'Jorge Machado', 'jorge.machado@uniluanda.edu.ao', 'jorge123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('51', 'Carolina Matos', 'carolina.matos@uniluanda.edu.ao', 'carolina123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('52', 'Alexandre Brito', 'alexandre.brito@uniluanda.edu.ao', 'alexandre123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('53', 'Beatriz Fonseca', 'beatriz.fonseca@uniluanda.edu.ao', 'beatriz123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('54', 'Sérgio Macedo', 'sergio.macedo@uniluanda.edu.ao', 'sergio123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('55', 'Mónica Vaz', 'monica.vaz@uniluanda.edu.ao', 'monica123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('56', 'Vítor Barros', 'vitor.barros@uniluanda.edu.ao', 'vitor123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('57', 'Diana Castro', 'diana.castro@uniluanda.edu.ao', 'diana123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('58', 'Bruno Andrade', 'bruno.andrade@uniluanda.edu.ao', 'bruno123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('59', 'Lúcia Simões', 'lucia.simoes@uniluanda.edu.ao', 'lucia123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('60', 'Hélder Figueiredo', 'helder.figueiredo@uniluanda.edu.ao', 'helder123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('61', 'Carla Coelho', 'carla.coelho@uniluanda.edu.ao', 'carla123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('62', 'David Melo', 'david.melo@uniluanda.edu.ao', 'david123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('63', 'Susana Torres', 'susana.torres@uniluanda.edu.ao', 'susana123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('64', 'Emanuel Moreira', 'emanuel.moreira@uniluanda.edu.ao', 'emanuel123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('65', 'Sandra Barbosa', 'sandra.barbosa@uniluanda.edu.ao', 'sandra123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('66', 'Gaspar Mendonça', 'gaspar.mendonca@uniluanda.edu.ao', 'gaspar123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('67', 'Palmira Nascimento', 'palmira.nascimento@uniluanda.edu.ao', 'palmira123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('68', 'John Smith', 'john.smith@uniluanda.edu.ao', 'john123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('69', 'Emma Johnson', 'emma.johnson@uniluanda.edu.ao', 'emma123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('70', 'Thomas Williams', 'thomas.williams@uniluanda.edu.ao', 'thomas123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('71', 'Sophie Brown', 'sophie.brown@uniluanda.edu.ao', 'sophie123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('72', 'Michael Jones', 'michael.jones@uniluanda.edu.ao', 'michael123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('73', 'Laura Garcia', 'laura.garcia@uniluanda.edu.ao', 'laura123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('74', 'James Miller', 'james.miller@uniluanda.edu.ao', 'james123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('75', 'Anna Davis', 'anna.davis@uniluanda.edu.ao', 'anna123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('76', 'William Rodriguez', 'william.rodriguez@uniluanda.edu.ao', 'william123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('77', 'Emily Martinez', 'emily.martinez@uniluanda.edu.ao', 'emily123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('78', 'David Wilson', 'david.wilson@uniluanda.edu.ao', 'david123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('79', 'Sarah Anderson', 'sarah.anderson@uniluanda.edu.ao', 'sarah123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('80', 'Richard Taylor', 'richard.taylor@uniluanda.edu.ao', 'richard123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('81', 'Jessica Thomas', 'jessica.thomas@uniluanda.edu.ao', 'jessica123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('82', 'Charles Moore', 'charles.moore@uniluanda.edu.ao', 'charles123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('83', 'Elizabeth Jackson', 'elizabeth.jackson@uniluanda.edu.ao', 'elizabeth123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('84', 'George White', 'george.white@uniluanda.edu.ao', 'george123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('85', 'Victoria Harris', 'victoria.harris@uniluanda.edu.ao', 'victoria123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('86', 'Paul Martin', 'paul.martin@uniluanda.edu.ao', 'paul123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('87', 'Helen Thompson', 'helen.thompson@uniluanda.edu.ao', 'helen123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('88', 'Mark Garcia', 'mark.garcia@uniluanda.edu.ao', 'mark123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('89', 'Lucy Robinson', 'lucy.robinson@uniluanda.edu.ao', 'lucy123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('90', 'Andrew Clark', 'andrew.clark@uniluanda.edu.ao', 'andrew123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('91', 'Rebecca Rodriguez', 'rebecca.rodriguez@uniluanda.edu.ao', 'rebecca123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('92', 'Christopher Lewis', 'christopher.lewis@uniluanda.edu.ao', 'christopher123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('93', 'Catherine Lee', 'catherine.lee@uniluanda.edu.ao', 'catherine123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('94', 'Peter Walker', 'peter.walker@uniluanda.edu.ao', 'peter123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('95', 'Margaret Hall', 'margaret.hall@uniluanda.edu.ao', 'margaret123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('96', 'Kevin Allen', 'kevin.allen@uniluanda.edu.ao', 'kevin123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('97', 'Patricia Young', 'patricia.young@uniluanda.edu.ao', 'patricia123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('98', 'Brian King', 'brian.king@uniluanda.edu.ao', 'brian123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('99', 'Susan Wright', 'susan.wright@uniluanda.edu.ao', 'susan123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('100', 'Edward Scott', 'edward.scott@uniluanda.edu.ao', 'edward123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('101', 'Karen Green', 'karen.green@uniluanda.edu.ao', 'karen123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('102', 'Ronald Adams', 'ronald.adams@uniluanda.edu.ao', 'ronald123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('103', 'Nancy Baker', 'nancy.baker@uniluanda.edu.ao', 'nancy123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('104', 'Kenneth Gonzalez', 'kenneth.gonzalez@uniluanda.edu.ao', 'kenneth123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('105', 'Lisa Nelson', 'lisa.nelson@uniluanda.edu.ao', 'lisa123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('106', 'Steven Carter', 'steven.carter@uniluanda.edu.ao', 'steven123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('107', 'Betty Mitchell', 'betty.mitchell@uniluanda.edu.ao', 'betty123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('108', 'Anthony Perez', 'anthony.perez@uniluanda.edu.ao', 'anthony123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('109', 'Dorothy Roberts', 'dorothy.roberts@uniluanda.edu.ao', 'dorothy123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('110', 'Robert Turner', 'robert.turner@uniluanda.edu.ao', 'robert123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('111', 'Jennifer Phillips', 'jennifer.phillips@uniluanda.edu.ao', 'jennifer123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('112', 'Daniel Campbell', 'daniel.campbell@uniluanda.edu.ao', 'daniel123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('113', 'Michelle Parker', 'michelle.parker@uniluanda.edu.ao', 'michelle123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('114', 'Matthew Evans', 'matthew.evans@uniluanda.edu.ao', 'matthew123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('115', 'Amy Edwards', 'amy.edwards@uniluanda.edu.ao', 'amy123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('116', 'Donald Collins', 'donald.collins@uniluanda.edu.ao', 'donald123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09'),
('117', 'Kimberly Stewart', 'kimberly.stewart@uniluanda.edu.ao', 'kimberly123', 'estudante', 'default.png', '2026-03-09 11:47:09', '2026-03-09 11:47:09');

SET FOREIGN_KEY_CHECKS = 1;

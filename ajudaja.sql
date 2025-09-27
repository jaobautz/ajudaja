-- ===================================================================
-- SCRIPT COMPLETO PARA CRIAÇÃO E POPULAÇÃO DO BANCO DE DADOS AJUDAJÁ
-- ===================================================================

-- Exclui o banco de dados se ele já existir, para começar do zero
DROP DATABASE IF EXISTS `ajudaja`;

-- Cria o banco de dados
CREATE DATABASE `ajudaja` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Seleciona o banco de dados para uso
USE `ajudaja`;

-- Define configurações da sessão SQL
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


-- --------------------------------------------------------
-- ESTRUTURA DA TABELA: usuarios
-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- ESTRUTURA DA TABELA: pedidos
-- --------------------------------------------------------

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `urgencia` enum('Urgente','Pode Esperar','Daqui a uma Semana') NOT NULL,
  `categoria` enum('Cesta Básica','Carona','Apoio Emocional','Doação de Itens','Serviços Voluntários','Outros') NOT NULL,
  `whatsapp_numero` varchar(20) NOT NULL,
  `status` enum('Aberto','Concluído') NOT NULL DEFAULT 'Aberto',
  `data_postagem` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- ADICIONANDO RESTRIÇÕES (CONSTRAINTS)
-- --------------------------------------------------------

ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- --------------------------------------------------------
-- INSERINDO DADOS DE EXEMPLO (DUMP DATA)
-- --------------------------------------------------------

--
-- Inserindo dados na tabela `usuarios`
-- NOTA: As senhas estão criptografadas (hashed), como o PHP faria.
-- A senha para todos os usuários de exemplo é "senha123"
--
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`) VALUES
(1, 'Maria Souza', 'maria.s@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(2, 'João Pedro', 'joao.p@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(3, 'Ana Clara', 'ana.c@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(4, 'Lucas Ferreira', 'lucas.f@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(5, 'Beatriz Lima', 'beatriz.l@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(6, 'Carlos Mendes', 'carlos.m@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(7, 'Juliana Costa', 'juliana.c@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K'),
(8, 'Rafael Gomes', 'rafael.g@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K');


--
-- Inserindo dados na tabela `pedidos`
--
INSERT INTO `pedidos` (`usuario_id`, `titulo`, `descricao`, `urgencia`, `categoria`, `whatsapp_numero`, `status`, `data_postagem`) VALUES
(1, 'Preciso de uma cesta básica urgente', 'Olá, sou mãe solo com dois filhos e estou desempregada. Qualquer ajuda com alimentos não perecíveis seria uma bênção. Muito obrigada!', 'Urgente', 'Cesta Básica', '11988887777', 'Aberto', '2025-09-26 21:10:00'),
(2, 'Carona para entrevista de emprego', 'Consegui uma entrevista de emprego no centro da cidade na próxima terça-feira às 9h, mas não tenho dinheiro para a condução. Alguém poderia me dar uma carona?', 'Pode Esperar', 'Carona', '21977776666', 'Aberto', '2025-09-26 15:20:00'),
(3, 'Busco alguém para conversar', 'Estou passando por um momento muito difícil e solitário. Gostaria de encontrar alguém disposto a conversar um pouco, para desabafar. Pode ser online.', 'Daqui a uma Semana', 'Apoio Emocional', '31966665555', 'Aberto', '2025-09-25 11:00:00'),
(4, 'Doação de roupas de frio para crianças', 'Meus filhos estão crescendo rápido e as roupas de frio do ano passado não servem mais. Se alguém tiver casacos ou calças para crianças de 5 e 8 anos, ficaria muito grata.', 'Urgente', 'Doação de Itens', '11988887777', 'Aberto', '2025-09-26 22:05:00'),
(6, 'Ajuda voluntária com aulas de matemática', 'Sou bom em matemática e gostaria de ajudar algum estudante do ensino fundamental que esteja com dificuldades. Posso ajudar online uma vez por semana.', 'Daqui a uma Semana', 'Serviços Voluntários', '21977776666', 'Aberto', '2025-09-24 18:00:00'),
(7, 'Preciso de um berço usado', 'Estou grávida e montando o enxoval do meu bebê. Se alguém tiver um berço em bom estado que não usa mais e puder doar, seria uma ajuda imensa.', 'Pode Esperar', 'Doação de Itens', '31966665555', 'Concluído', '2025-09-20 09:30:00'),
(8, 'Ajuda para consertar vazamento', 'Minha pia da cozinha está com um vazamento que não consigo consertar e não tenho como pagar um encanador agora. Alguém com conhecimento poderia me ajudar?', 'Urgente', 'Serviços Voluntários', '11988887777', 'Aberto', '2025-09-26 14:00:00');


-- Finaliza a transação
COMMIT;
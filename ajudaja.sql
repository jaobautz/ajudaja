-- ===================================================================
-- SCRIPT COMPLETO PARA POSTGRESQL - PROJETO AJUDAJÁ
-- INSTRUÇÕES: Crie um banco de dados vazio chamado "ajudaja"
-- e execute este script dentro dele.
-- ===================================================================

-- Remove as tabelas se elas já existirem para evitar erros
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS usuarios;

-- --------------------------------------------------------
-- ESTRUTURA DA TABELA: usuarios
-- --------------------------------------------------------
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  data_cadastro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- ESTRUTURA DA TABELA: pedidos
-- --------------------------------------------------------
CREATE TABLE pedidos (
  id SERIAL PRIMARY KEY,
  usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT NOT NULL,
  urgencia VARCHAR(50) NOT NULL CHECK (urgencia IN ('Urgente', 'Pode Esperar', 'Daqui a uma Semana')),
  categoria VARCHAR(50) NOT NULL CHECK (categoria IN ('Cesta Básica', 'Carona', 'Apoio Emocional', 'Doação de Itens', 'Serviços Voluntários', 'Outros')),
  whatsapp_numero VARCHAR(20) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'Aberto' CHECK (status IN ('Aberto', 'Concluído')),
  data_postagem TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- ESTRUTURA DA TABELA: comentarios
-- --------------------------------------------------------
CREATE TABLE comentarios (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    comentario TEXT NOT NULL,
    data_comentario TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- --------------------------------------------------------
-- INSERINDO DADOS DE EXEMPLO (DUMP DATA)
-- --------------------------------------------------------

--
-- Inserindo dados na tabela `usuarios`
-- A senha para todos os usuários de exemplo é "senha123"
--
INSERT INTO usuarios (id, nome, email, senha) VALUES
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
INSERT INTO pedidos (id, usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero, status, data_postagem) VALUES
(1, 1, 'Preciso de uma cesta básica urgente', 'Olá, sou mãe solo com dois filhos e estou desempregada. Qualquer ajuda com alimentos não perecíveis seria uma bênção. Muito obrigada!', 'Urgente', 'Cesta Básica', '11988887777', 'Aberto', '2025-09-26 21:10:00'),
(2, 2, 'Carona para entrevista de emprego', 'Consegui uma entrevista de emprego no centro da cidade na próxima terça-feira às 9h, mas não tenho dinheiro para a condução. Alguém poderia me dar uma carona?', 'Pode Esperar', 'Carona', '21977776666', 'Aberto', '2025-09-26 15:20:00'),
(3, 3, 'Busco alguém para conversar', 'Estou passando por um momento muito difícil e solitário. Gostaria de encontrar alguém disposto a conversar um pouco, para desabafar. Pode ser online.', 'Daqui a uma Semana', 'Apoio Emocional', '31966665555', 'Aberto', '2025-09-25 11:00:00'),
(4, 4, 'Doação de roupas de frio para crianças', 'Meus filhos estão crescendo rápido e as roupas de frio do ano passado não servem mais. Se alguém tiver casacos ou calças para crianças de 5 e 8 anos, ficaria muito grata.', 'Urgente', 'Doação de Itens', '11988887777', 'Aberto', '2025-09-26 22:05:00'),
(5, 6, 'Ajuda voluntária com aulas de matemática', 'Sou bom em matemática e gostaria de ajudar algum estudante do ensino fundamental que esteja com dificuldades. Posso ajudar online uma vez por semana.', 'Daqui a uma Semana', 'Serviços Voluntários', '21977776666', 'Aberto', '2025-09-24 18:00:00'),
(6, 7, 'Preciso de um berço usado', 'Estou grávida e montando o enxoval do meu bebê. Se alguém tiver um berço em bom estado que não usa mais e puder doar, seria uma ajuda imensa.', 'Pode Esperar', 'Doação de Itens', '31966665555', 'Concluído', '2025-09-20 09:30:00'),
(7, 8, 'Ajuda para consertar vazamento', 'Minha pia da cozinha está com um vazamento que não consigo consertar e não tenho como pagar um encanador agora. Alguém com conhecimento poderia me ajudar?', 'Urgente', 'Serviços Voluntários', '11988887777', 'Aberto', '2025-09-26 14:00:00');

-- Sincroniza os contadores de ID para evitar conflitos em futuras inserções
SELECT setval('usuarios_id_seq', (SELECT MAX(id) FROM usuarios));
SELECT setval('pedidos_id_seq', (SELECT MAX(id) FROM pedidos));
SELECT setval('comentarios_id_seq', (SELECT MAX(id) FROM comentarios), false); -- O 'false' significa que o próximo ID será o valor atual + 1
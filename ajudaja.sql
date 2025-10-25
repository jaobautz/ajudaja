
DROP TABLE IF EXISTS mensagens;
DROP TABLE IF EXISTS conversas;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  telefone VARCHAR(20) NOT NULL,
  reputacao INT NOT NULL DEFAULT 0, -- Coluna para Sistema de Reputação
  data_cadastro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    urgencia VARCHAR(50) NOT NULL CHECK (urgencia IN ('Urgente', 'Pode Esperar', 'Daqui a uma Semana')),
    categoria VARCHAR(50) NOT NULL CHECK (categoria IN ('Cesta Básica', 'Carona', 'Apoio Emocional', 'Doação de Itens', 'Serviços Voluntários', 'Outros')),
    whatsapp_numero VARCHAR(20) NOT NULL, -- Mantido por enquanto, chat é a prioridade
    status VARCHAR(50) NOT NULL DEFAULT 'Aberto' CHECK (status IN ('Aberto', 'Concluído')),
    data_postagem TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Campos de Geolocalização
    cep VARCHAR(10) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL
);

CREATE TABLE comentarios (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    parent_id INT NULL REFERENCES comentarios(id) ON DELETE CASCADE, 
    data_comentario TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE conversas (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_criador_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    usuario_voluntario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    status_conversa VARCHAR(50) NOT NULL DEFAULT 'Aberta' CHECK (status_conversa IN ('Aberta', 'Ajuda Confirmada')), -- Para Reputação
    data_criacao TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pedido_id, usuario_voluntario_id)
);

CREATE TABLE mensagens (
    id SERIAL PRIMARY KEY,
    conversa_id INT NOT NULL REFERENCES conversas(id) ON DELETE CASCADE,
    remetente_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lida BOOLEAN NOT NULL DEFAULT false 
);

INSERT INTO usuarios (id, nome, email, senha, telefone, reputacao) VALUES
(1, 'Maria Souza', 'maria.s@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '11988887777', 0),
(2, 'João Pedro', 'joao.p@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '21977776666', 0),
(3, 'Ana Clara', 'ana.c@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '31966665555', 0),
(4, 'Lucas Ferreira', 'lucas.f@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '41955554444', 0),
(5, 'Beatriz Lima', 'beatriz.l@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '51944443333', 0),
(6, 'Carlos Mendes', 'carlos.m@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '61933332222', 0),
(7, 'Juliana Costa', 'juliana.c@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '71922221111', 0),
(8, 'Rafael Gomes', 'rafael.g@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '81911110000', 0);

INSERT INTO pedidos (id, usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero, status, data_postagem, cep, cidade, estado) VALUES
(1, 1, 'Preciso de uma cesta básica urgente', 'Olá, sou mãe solo com dois filhos...', 'Urgente', 'Cesta Básica', '11988887777', 'Aberto', '2025-10-20 21:10:00', '01001-000', 'São Paulo', 'SP'), -- Exemplo com CEP
(2, 2, 'Carona para entrevista de emprego', 'Consegui uma entrevista...', 'Pode Esperar', 'Carona', '21977776666', 'Aberto', '2025-10-21 15:20:00', '20040-004', 'Rio de Janeiro', 'RJ'), -- Exemplo com CEP
(3, 3, 'Busco alguém para conversar', 'Estou passando por um momento...', 'Daqui a uma Semana', 'Apoio Emocional', '31966665555', 'Aberto', '2025-10-22 11:00:00', NULL, NULL, NULL), -- Exemplo sem CEP
(4, 4, 'Doação de roupas de frio para crianças', 'Meus filhos estão crescendo...', 'Urgente', 'Doação de Itens', '11988887777', 'Aberto', '2025-10-22 22:05:00', '01001-000', 'São Paulo', 'SP'),
(5, 6, 'Ajuda voluntária com aulas de matemática', 'Sou bom em matemática...', 'Daqui a uma Semana', 'Serviços Voluntários', '21977776666', 'Aberto', '2025-10-23 18:00:00', NULL, NULL, NULL),
(6, 7, 'Preciso de um berço usado', 'Estou grávida...', 'Pode Esperar', 'Doação de Itens', '31966665555', 'Concluído', '2025-10-10 09:30:00', '30110-000', 'Belo Horizonte', 'MG'),
(7, 8, 'Ajuda para consertar vazamento', 'Minha pia...', 'Urgente', 'Serviços Voluntários', '11988887777', 'Aberto', '2025-10-24 14:00:00', NULL, NULL, NULL);


INSERT INTO comentarios (pedido_id, usuario_id, parent_id, comentario, data_comentario) VALUES
(1, 2, NULL, 'Oi Maria, posso ajudar com alguns itens. Onde posso entregar?', '2025-10-20 22:00:00'),
(1, 1, 1, 'Muito obrigada, João! Pode ser no endereço X?', '2025-10-20 22:15:00'),
(2, 5, NULL, 'Que horas é a entrevista? Posso estar passando pelo centro nesse horário.', '2025-10-21 16:00:00');

CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION IF NOT EXISTS btree_gist;

CREATE INDEX idx_pedidos_localizacao ON pedidos USING gist (latitude, longitude);

CREATE INDEX idx_pedidos_usuario_id ON pedidos(usuario_id);
CREATE INDEX idx_comentarios_pedido_id ON comentarios(pedido_id);
CREATE INDEX idx_comentarios_usuario_id ON comentarios(usuario_id);
CREATE INDEX idx_comentarios_parent_id ON comentarios(parent_id);
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedidos_urgencia ON pedidos(urgencia);
CREATE INDEX idx_pedidos_categoria ON pedidos(categoria);
CREATE INDEX idx_pedidos_cidade_estado ON pedidos(cidade, estado); -- Índice para filtro por cidade/estado
CREATE INDEX idx_pedidos_busca_texto ON pedidos USING gin (titulo gin_trgm_ops, descricao gin_trgm_ops);
CREATE INDEX idx_conversas_usuario_criador_id ON conversas(usuario_criador_id);
CREATE INDEX idx_conversas_usuario_voluntario_id ON conversas(usuario_voluntario_id);
CREATE INDEX idx_conversas_status ON conversas(status_conversa);
CREATE INDEX idx_mensagens_conversa_id ON mensagens(conversa_id);
CREATE INDEX idx_mensagens_remetente_id ON mensagens(remetente_id);

SELECT setval('usuarios_id_seq', COALESCE((SELECT MAX(id) FROM usuarios), 1), COALESCE((SELECT MAX(id) FROM usuarios), 1) IS NOT NULL);
SELECT setval('pedidos_id_seq', COALESCE((SELECT MAX(id) FROM pedidos), 1), COALESCE((SELECT MAX(id) FROM pedidos), 1) IS NOT NULL);
SELECT setval('comentarios_id_seq', COALESCE((SELECT MAX(id) FROM comentarios), 1), COALESCE((SELECT MAX(id) FROM comentarios), 1) IS NOT NULL);
SELECT setval('conversas_id_seq', COALESCE((SELECT MAX(id) FROM conversas), 1), COALESCE((SELECT MAX(id) FROM conversas), 1) IS NOT NULL);
SELECT setval('mensagens_id_seq', COALESCE((SELECT MAX(id) FROM mensagens), 1), COALESCE((SELECT MAX(id) FROM mensagens), 1) IS NOT NULL);


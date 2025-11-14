-- ===================================================================
-- SETUP DE EXTENSÕES
-- ===================================================================
CREATE EXTENSION IF NOT EXISTS pg_trgm;       -- Para busca de texto rápida (ILIKE)
CREATE EXTENSION IF NOT EXISTS btree_gist;    -- Dependência para o índice GIST
CREATE EXTENSION IF NOT EXISTS cube;          -- Dependência para earthdistance
CREATE EXTENSION IF NOT EXISTS earthdistance; -- Para cálculo de distância geográfica

-- ===================================================================
-- LIMPEZA DO ESQUEMA (SCHEMA)
-- Remove tabelas na ordem inversa de dependência.
-- ===================================================================
DROP TABLE IF EXISTS mensagens;
DROP TABLE IF EXISTS avaliacoes; -- Tabela de avaliações deve ser dropada antes de conversas/usuarios
DROP TABLE IF EXISTS conversas;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS usuarios;

-- ===================================================================
--  CRIAÇÃO DAS TABELAS (SCHEMA)
-- ===================================================================

-- Tabela principal de usuários
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  telefone VARCHAR(20) NOT NULL,
  -- Coluna 'reputacao' foi REMOVIDA
  data_cadastro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabela principal de pedidos de ajuda
CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    urgencia VARCHAR(50) NOT NULL CHECK (urgencia IN ('Urgente', 'Pode Esperar', 'Daqui a uma Semana')),
    categoria VARCHAR(50) NOT NULL CHECK (categoria IN ('Cesta Básica', 'Carona', 'Apoio Emocional', 'Doação de Itens', 'Serviços Voluntários', 'Outros')),
    whatsapp_numero VARCHAR(20) NOT NULL, 
    status VARCHAR(50) NOT NULL DEFAULT 'Aberto' CHECK (status IN ('Aberto', 'Concluído')),
    data_postagem TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cep VARCHAR(10) NULL,
    cidade VARCHAR(100) NULL,
    estado VARCHAR(2) NULL,
    latitude DECIMAL(10, 8) NULL, 
    longitude DECIMAL(11, 8) NULL 
);

-- Tabela para a discussão pública (aninhada)
CREATE TABLE comentarios (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    parent_id INT NULL REFERENCES comentarios(id) ON DELETE CASCADE, 
    comentario TEXT NOT NULL,
    data_comentario TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para as "salas" de chat privado
CREATE TABLE conversas (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_criador_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE, 
    usuario_voluntario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE, 
    status_conversa VARCHAR(50) NOT NULL DEFAULT 'Aberta' CHECK (status_conversa IN ('Aberta', 'Ajuda Confirmada')), -- 'Ajuda Confirmada' agora significa que FOI AVALIADA
    data_criacao TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(pedido_id, usuario_voluntario_id) 
);

-- Tabela para as mensagens individuais do chat
CREATE TABLE mensagens (
    id SERIAL PRIMARY KEY,
    conversa_id INT NOT NULL REFERENCES conversas(id) ON DELETE CASCADE,
    remetente_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE, 
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lida BOOLEAN NOT NULL DEFAULT false 
);

-- ===================================================================
-- NOVA TABELA: avaliacoes
-- Armazena a nota (estrelas) e o comentário da avaliação
-- ===================================================================
CREATE TABLE avaliacoes (
    id SERIAL PRIMARY KEY,
    conversa_id INT NOT NULL REFERENCES conversas(id) ON DELETE CASCADE,     -- A qual conversa essa avaliação pertence
    avaliador_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,   -- Quem DEU a avaliação (o criador do pedido)
    avaliado_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,    -- Quem RECEBEU a avaliação (o voluntário)
    nota INT NOT NULL CHECK (nota >= 1 AND nota <= 5),                 -- Nota de 1 a 5 estrelas
    comentario_avaliacao TEXT NULL,                                    -- O texto da avaliação (opcional)
    data_avaliacao TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(conversa_id) -- Garante que uma conversa só possa ser avaliada uma vez
);


-- ===================================================================
--CRIAÇÃO DE ÍNDICES DE CHAVE ESTRANGEIRA (FK)
-- ===================================================================
CREATE INDEX idx_pedidos_usuario_id ON pedidos(usuario_id);
CREATE INDEX idx_comentarios_pedido_id ON comentarios(pedido_id);
CREATE INDEX idx_comentarios_usuario_id ON comentarios(usuario_id);
CREATE INDEX idx_comentarios_parent_id ON comentarios(parent_id);
CREATE INDEX idx_conversas_usuario_criador_id ON conversas(usuario_criador_id);
CREATE INDEX idx_conversas_usuario_voluntario_id ON conversas(usuario_voluntario_id);
CREATE INDEX idx_mensagens_conversa_id ON mensagens(conversa_id);
CREATE INDEX idx_mensagens_remetente_id ON mensagens(remetente_id);
CREATE INDEX idx_avaliacoes_conversa_id ON avaliacoes(conversa_id); -- Novo
CREATE INDEX idx_avaliacoes_avaliador_id ON avaliacoes(avaliador_id); -- Novo
CREATE INDEX idx_avaliacoes_avaliado_id ON avaliacoes(avaliado_id); -- Novo


-- ===================================================================
--CRIAÇÃO DE ÍNDICES DE PERFORMANCE (FILTROS E BUSCAS)
-- ===================================================================
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedidos_urgencia ON pedidos(urgencia);
CREATE INDEX idx_pedidos_categoria ON pedidos(categoria);
CREATE INDEX idx_pedidos_cidade_estado ON pedidos(cidade, estado);
CREATE INDEX idx_conversas_status ON conversas(status_conversa);
CREATE INDEX idx_pedidos_busca_texto ON pedidos USING gin (titulo gin_trgm_ops, descricao gin_trgm_ops);
CREATE INDEX idx_pedidos_localizacao ON pedidos USING gist (ll_to_earth(latitude, longitude));

-- ===================================================================
-- SEÇÃO 4: DADOS DE EXEMPLO (SEEDS)
-- Inserimos dados com IDs manuais para garantir consistência
-- nas chaves estrangeiras (parent_id, conversa_id, etc.).
-- ===================================================================

-- Senha para todos os usuários: "senha123"
-- Hash: $2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K
INSERT INTO usuarios (id, nome, email, senha, telefone) VALUES
(1, 'Maria Souza', 'maria.s@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '11988887777'),
(2, 'João Pedro', 'joao.p@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '21977776666'),
(3, 'Ana Clara', 'ana.c@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '31966665555'),
(4, 'Lucas Ferreira', 'lucas.f@email.com', '$2y$10$T8hsL2s.aF8q.1J.H7B.Q.hZ1.dE9.U/5ztnjOaWp3vUq1XyY2W9K', '41955554444');

-- Pedidos com dados de Geolocalização (Lat/Lon)
INSERT INTO pedidos (id, usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero, status, data_postagem, cep, cidade, estado, latitude, longitude) VALUES
(1, 1, 'Preciso de uma cesta básica urgente', 'Olá, sou mãe solo com dois filhos e estou desempregada. Meu gás acabou e não tenho o que cozinhar para eles hoje. Qualquer ajuda com alimentos não perecíveis (arroz, feijão, macarrão) e um botijão de gás seria uma bênção.', 'Urgente', 'Cesta Básica', '11988887777', 'Aberto', '2025-10-20 21:10:00', '01001-000', 'São Paulo', 'SP', -23.550520, -46.633308),
(2, 3, 'Carona para hospital (Quimioterapia)', 'Consegui uma vaga para minha mãe fazer quimioterapia no INCA na próxima terça-feira, mas não tenho dinheiro para o Uber. Alguém com bom coração poderia me levar? Moramos em Copacabana.', 'Pode Esperar', 'Carona', '31966665555', 'Aberto', '2025-10-21 15:20:00', '22010-000', 'Rio de Janeiro', 'RJ', -22.966996, -43.180496),
(3, 1, 'Ajuda para consertar vazamento', 'Minha pia da cozinha está com um vazamento que não consigo consertar e não tenho como pagar um encanador agora. A conta de água vai vir um absurdo. Alguém com conhecimento poderia me ajudar?', 'Urgente', 'Serviços Voluntários', '11988887777', 'Aberto', '2025-10-22 11:00:00', NULL, NULL, NULL, NULL, NULL);

-- Comentários Aninhados (Threaded Comments)
INSERT INTO comentarios (id, pedido_id, usuario_id, parent_id, comentario, data_comentario) VALUES
(1, 1, 2, NULL, 'Oi Maria, sou o João. Tenho um botijão de gás reserva aqui em casa. Posso deixar com você amanhã cedo?', '2025-10-20 22:00:00'), -- Comentário Raiz (ID 1)
(2, 1, 1, 1, 'Nossa, João, sério? Isso salvaria minha vida! Você é um anjo! Pode sim. Vou iniciar um chat com você para combinarmos o endereço.', '2025-10-20 22:15:00'), -- Resposta ao Comentário 1
(3, 1, 4, NULL, 'Maria, eu não tenho um botijão, mas posso ajudar com a cesta básica. Trabalho perto da Sé, consigo deixar aí na hora do almoço?', '2025-10-21 09:00:00'), -- Comentário Raiz (ID 3)
(4, 2, 4, NULL, 'Oi Ana, sou o Lucas. Meu pai faz tratamento lá, conheço bem o caminho. Que horas vocês precisam estar lá?', '2025-10-21 16:00:00'); -- Comentário Raiz (ID 4)

-- Conversas de Chat
INSERT INTO conversas (id, pedido_id, usuario_criador_id, usuario_voluntario_id, status_conversa) VALUES
(1, 1, 1, 2, 'Aberta'),        -- Conversa sobre Pedido 1 (Cesta): Maria (Criadora) e João (Voluntário)
(2, 2, 3, 4, 'Ajuda Confirmada'); -- Conversa sobre Pedido 2 (Carona): Ana (Criadora) e Lucas (Voluntário)

-- Mensagens do Chat
INSERT INTO mensagens (id, conversa_id, remetente_id, mensagem) VALUES
(1, 1, 2, 'Oi Maria, aqui é o João do comentário. Pode me passar seu endereço completo?'), -- João (ID 2) envia
(2, 1, 1, 'Oi João! Claro, é Rua da Ajuda, nº 123. Muito obrigada mesmo!'), -- Maria (ID 1) responde
(3, 2, 4, 'Ana, aqui é o Lucas. Vi seu pedido de carona para o INCA.'), -- Lucas (ID 4) envia
(4, 2, 4, 'Posso buscar vocês na terça, sem problemas. A consulta é às 9h, né? Posso passar aí às 7h30 para irmos com calma.'),
(5, 2, 3, 'Lucas, você não sabe o quanto isso significa! Muito obrigada! 7h30 está perfeito. Deus te abençoe.'), -- Ana (ID 3) responde
(6, 2, 4, 'Imagina, estamos juntos nessa. :) Até terça!');

-- Avaliações (Explorando a novidade)
-- Ana (ID 3) avalia Lucas (ID 4) pela ajuda na Conversa 2
INSERT INTO avaliacoes (id, conversa_id, avaliador_id, avaliado_id, nota, comentario_avaliacao) VALUES
(1, 2, 3, 4, 5, 'O Lucas foi um verdadeiro anjo! Foi super pontual, atencioso e tornou um dia difícil muito mais leve para minha mãe. Não tenho como agradecer o suficiente. Voluntário nota 1000!');

-- ===================================================================
-- SINCRONIZAÇÃO DAS SEQUÊNCIAS (SERIAL IDs)
-- ===================================================================
SELECT setval('usuarios_id_seq', COALESCE((SELECT MAX(id) FROM usuarios), 1), COALESCE((SELECT MAX(id) FROM usuarios), 1) IS NOT NULL);
SELECT setval('pedidos_id_seq', COALESCE((SELECT MAX(id) FROM pedidos), 1), COALESCE((SELECT MAX(id) FROM pedidos), 1) IS NOT NULL);
SELECT setval('comentarios_id_seq', COALESCE((SELECT MAX(id) FROM comentarios), 1), COALESCE((SELECT MAX(id) FROM comentarios), 1) IS NOT NULL);
SELECT setval('conversas_id_seq', COALESCE((SELECT MAX(id) FROM conversas), 1), COALESCE((SELECT MAX(id) FROM conversas), 1) IS NOT NULL);
SELECT setval('mensagens_id_seq', COALESCE((SELECT MAX(id) FROM mensagens), 1), COALESCE((SELECT MAX(id) FROM mensagens), 1) IS NOT NULL);
SELECT setval('avaliacoes_id_seq', COALESCE((SELECT MAX(id) FROM avaliacoes), 1), COALESCE((SELECT MAX(id) FROM avaliacoes), 1) IS NOT NULL); -- Novo
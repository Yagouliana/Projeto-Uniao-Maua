-- Criar banco de dados para União Mauá Futsal
CREATE DATABASE IF NOT EXISTS uniao_maua_futsal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE uniao_maua_futsal;

-- Tabela para armazenar agendamentos de testes
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    data_nascimento DATE NOT NULL,
    categoria ENUM('sub-9', 'sub-11', 'sub-13', 'sub-15', 'sub-17', 'sub-20') NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    responsavel VARCHAR(255) DEFAULT NULL,
    experiencia TEXT DEFAULT NULL,
    data_agendamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'confirmado', 'realizado', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT DEFAULT NULL,
    data_teste DATETIME DEFAULT NULL,
    avaliacao TEXT DEFAULT NULL,
    resultado ENUM('aprovado', 'reprovado', 'pendente') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_categoria (categoria),
    INDEX idx_status (status),
    INDEX idx_data_agendamento (data_agendamento)
);

-- Tabela para armazenar informações dos atletas aprovados
CREATE TABLE IF NOT EXISTS atletas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT,
    nome VARCHAR(255) NOT NULL,
    data_nascimento DATE NOT NULL,
    categoria VARCHAR(20) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    responsavel VARCHAR(255) DEFAULT NULL,
    endereco TEXT DEFAULT NULL,
    numero_camisa INT DEFAULT NULL,
    posicao VARCHAR(50) DEFAULT NULL,
    data_ingresso DATE DEFAULT NULL,
    status ENUM('ativo', 'inativo', 'suspenso') DEFAULT 'ativo',
    observacoes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL,
    INDEX idx_categoria (categoria),
    INDEX idx_status (status),
    INDEX idx_nome (nome)
);

-- Tabela para armazenar treinos e presenças
CREATE TABLE IF NOT EXISTS treinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(20) NOT NULL,
    data_treino DATE NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    local VARCHAR(255) DEFAULT 'Quadra Principal',
    tipo ENUM('treino', 'jogo', 'teste') DEFAULT 'treino',
    observacoes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_categoria (categoria),
    INDEX idx_data (data_treino),
    INDEX idx_tipo (tipo)
);

-- Tabela para controle de presenças
CREATE TABLE IF NOT EXISTS presencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atleta_id INT NOT NULL,
    treino_id INT NOT NULL,
    presente BOOLEAN DEFAULT FALSE,
    justificativa TEXT DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (atleta_id) REFERENCES atletas(id) ON DELETE CASCADE,
    FOREIGN KEY (treino_id) REFERENCES treinos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_presenca (atleta_id, treino_id),
    INDEX idx_atleta (atleta_id),
    INDEX idx_treino (treino_id)
);

-- Tabela para armazenar jogos e competições
CREATE TABLE IF NOT EXISTS jogos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(20) NOT NULL,
    adversario VARCHAR(255) NOT NULL,
    data_jogo DATETIME NOT NULL,
    local VARCHAR(255) NOT NULL,
    tipo ENUM('amistoso', 'campeonato', 'copa', 'torneio') NOT NULL,
    placar_uniao INT DEFAULT NULL,
    placar_adversario INT DEFAULT NULL,
    resultado ENUM('vitoria', 'empate', 'derrota') DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_categoria (categoria),
    INDEX idx_data (data_jogo),
    INDEX idx_tipo (tipo)
);

-- Tabela para estatísticas dos atletas nos jogos
CREATE TABLE IF NOT EXISTS estatisticas_jogos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT NOT NULL,
    atleta_id INT NOT NULL,
    gols INT DEFAULT 0,
    assistencias INT DEFAULT 0,
    cartoes_amarelos INT DEFAULT 0,
    cartoes_vermelhos INT DEFAULT 0,
    minutos_jogados INT DEFAULT 0,
    observacoes TEXT DEFAULT NULL,
    
    FOREIGN KEY (jogo_id) REFERENCES jogos(id) ON DELETE CASCADE,
    FOREIGN KEY (atleta_id) REFERENCES atletas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_estatistica (jogo_id, atleta_id),
    INDEX idx_jogo (jogo_id),
    INDEX idx_atleta (atleta_id)
);

-- Tabela para mensagens de contato
CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('novo', 'lido', 'respondido') DEFAULT 'novo',
    data_contato TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resposta TIMESTAMP NULL DEFAULT NULL,
    resposta TEXT DEFAULT NULL,
    
    INDEX idx_status (status),
    INDEX idx_data (data_contato),
    INDEX idx_email (email)
);

-- Inserir alguns dados de exemplo
INSERT INTO agendamentos (nome, data_nascimento, categoria, telefone, email, responsavel, experiencia, status) VALUES
('João Silva Santos', '2010-05-15', 'sub-13', '(11) 99999-1234', 'joao.silva@email.com', 'Maria Silva Santos', 'Joga futsal há 2 anos na escola', 'pendente'),
('Pedro Oliveira', '2008-08-22', 'sub-15', '(11) 98888-5678', 'pedro.oliveira@email.com', 'Carlos Oliveira', 'Participou de escolinhas e torneios locais', 'confirmado'),
('Lucas Ferreira', '2006-12-10', 'sub-17', '(11) 97777-9012', 'lucas.ferreira@email.com', NULL, 'Jogou em outros clubes da região por 3 anos', 'realizado');

-- Inserir dados de exemplo para atletas (baseado nos agendamentos aprovados)
INSERT INTO atletas (agendamento_id, nome, data_nascimento, categoria, telefone, email, responsavel, numero_camisa, posicao, data_ingresso) VALUES
(3, 'Lucas Ferreira', '2006-12-10', 'sub-17', '(11) 97777-9012', 'lucas.ferreira@email.com', NULL, 10, 'Pivô', '2024-01-15');

-- Inserir treinos de exemplo
INSERT INTO treinos (categoria, data_treino, horario_inicio, horario_fim, tipo) VALUES
('sub-13', '2024-01-20', '14:00:00', '16:00:00', 'treino'),
('sub-15', '2024-01-20', '16:30:00', '18:30:00', 'treino'),
('sub-17', '2024-01-20', '19:00:00', '21:00:00', 'treino'),
('sub-13', '2024-01-22', '14:00:00', '16:00:00', 'treino'),
('sub-15', '2024-01-22', '16:30:00', '18:30:00', 'treino');

-- Inserir jogos de exemplo
INSERT INTO jogos (categoria, adversario, data_jogo, local, tipo, placar_uniao, placar_adversario, resultado) VALUES
('sub-17', 'São Caetano Futsal', '2024-01-25 19:00:00', 'Quadra Principal - União Mauá', 'campeonato', 4, 2, 'vitoria'),
('sub-15', 'Santo André FC', '2024-01-28 16:00:00', 'Ginásio Municipal Santo André', 'campeonato', 1, 1, 'empate');

-- Criar usuário administrador (opcional)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'treinador', 'assistente') DEFAULT 'assistente',
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
);

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@uniaomauafutsal.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Criar views úteis para relatórios
CREATE VIEW view_agendamentos_pendentes AS
SELECT 
    a.id,
    a.nome,
    a.categoria,
    a.telefone,
    a.email,
    a.data_agendamento,
    TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) as idade
FROM agendamentos a 
WHERE a.status = 'pendente'
ORDER BY a.data_agendamento DESC;

CREATE VIEW view_atletas_ativos AS
SELECT 
    at.id,
    at.nome,
    at.categoria,
    at.numero_camisa,
    at.posicao,
    TIMESTAMPDIFF(YEAR, at.data_nascimento, CURDATE()) as idade,
    at.data_ingresso
FROM atletas at 
WHERE at.status = 'ativo'
ORDER BY at.categoria, at.nome;

-- Procedimento para calcular estatísticas de um atleta
DELIMITER //
CREATE PROCEDURE GetEstatisticasAtleta(IN atleta_id INT)
BEGIN
    SELECT 
        a.nome,
        a.categoria,
        COUNT(ej.jogo_id) as jogos_disputados,
        SUM(ej.gols) as total_gols,
        SUM(ej.assistencias) as total_assistencias,
        SUM(ej.minutos_jogados) as total_minutos,
        AVG(ej.minutos_jogados) as media_minutos_por_jogo
    FROM atletas a
    LEFT JOIN estatisticas_jogos ej ON a.id = ej.atleta_id
    WHERE a.id = atleta_id
    GROUP BY a.id, a.nome, a.categoria;
END //
DELIMITER ;

-- Trigger para atualizar automaticamente o status do agendamento quando um atleta é criado
DELIMITER //
CREATE TRIGGER after_atleta_insert 
AFTER INSERT ON atletas
FOR EACH ROW
BEGIN
    IF NEW.agendamento_id IS NOT NULL THEN
        UPDATE agendamentos 
        SET status = 'realizado', resultado = 'aprovado'
        WHERE id = NEW.agendamento_id;
    END IF;
END //
DELIMITER ;

-- Comentários das tabelas
ALTER TABLE agendamentos COMMENT = 'Tabela para armazenar agendamentos de testes dos candidatos';
ALTER TABLE atletas COMMENT = 'Tabela para armazenar informações dos atletas aprovados e ativos no clube';
ALTER TABLE treinos COMMENT = 'Tabela para controlar treinos, jogos e eventos do clube';
ALTER TABLE presencas COMMENT = 'Tabela para controle de presença dos atletas nos treinos';
ALTER TABLE jogos COMMENT = 'Tabela para registrar jogos e competições do clube';
ALTER TABLE estatisticas_jogos COMMENT = 'Tabela para estatísticas individuais dos atletas nos jogos';
ALTER TABLE contatos COMMENT = 'Tabela para mensagens de contato recebidas pelo site';
ALTER TABLE usuarios COMMENT = 'Tabela para usuários do sistema administrativo';
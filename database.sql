-- EnterClinic - Medicina Natural
-- Execute este script no phpMyAdmin ou via linha de comando MySQL/MariaDB

CREATE DATABASE IF NOT EXISTS enterclinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE enterclinic;

CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE,
    sexo ENUM('M','F','O') DEFAULT 'M',
    cpf VARCHAR(14),
    celular VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(200),
    cidade VARCHAR(100),
    profissao VARCHAR(100),
    estado_civil VARCHAR(30),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS anamnese (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    queixa_principal TEXT,
    historia_doenca TEXT,
    antecedentes_pessoais TEXT,
    antecedentes_familiares TEXT,
    habitos TEXT,
    alimentacao TEXT,
    sono TEXT,
    atividade_fisica TEXT,
    medicamentos TEXT,
    alergias TEXT,
    exame_fisico TEXT,
    hipotese_diagnostica TEXT,
    conduta TEXT,
    campos_extras TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prontuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    data_consulta DATE NOT NULL,
    tipo ENUM('Consulta','Retorno','Urgência') DEFAULT 'Consulta',
    evolucao TEXT,
    prescricao TEXT,
    exames TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    tipo ENUM('Consulta','Retorno','Urgência') DEFAULT 'Consulta',
    status ENUM('Agendado','Confirmado','Realizado','Cancelado') DEFAULT 'Agendado',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campos_anamnese (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    label VARCHAR(100) NOT NULL,
    tipo ENUM('textarea','text','select') DEFAULT 'textarea',
    opcoes TEXT,
    obrigatorio TINYINT(1) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0
) ENGINE=InnoDB;

-- Campos padrão da anamnese
INSERT INTO campos_anamnese (nome, label, tipo, obrigatorio, ativo, ordem) VALUES
('queixa_principal',      'Queixa Principal',             'textarea', 1, 1, 1),
('historia_doenca',       'História da Doença Atual',     'textarea', 0, 1, 2),
('antecedentes_pessoais', 'Antecedentes Pessoais',        'textarea', 0, 1, 3),
('antecedentes_familiares','Antecedentes Familiares',     'textarea', 0, 1, 4),
('habitos',               'Hábitos e Estilo de Vida',     'textarea', 0, 1, 5),
('alimentacao',           'Alimentação',                  'textarea', 0, 1, 6),
('sono',                  'Qualidade do Sono',            'textarea', 0, 1, 7),
('atividade_fisica',      'Atividade Física',             'textarea', 0, 1, 8),
('medicamentos',          'Medicamentos / Suplementos',   'textarea', 0, 1, 9),
('alergias',              'Alergias',                     'text',     0, 1, 10),
('exame_fisico',          'Exame Físico',                 'textarea', 0, 1, 11),
('hipotese_diagnostica',  'Hipótese Diagnóstica',         'textarea', 0, 1, 12),
('conduta',               'Conduta Terapêutica',          'textarea', 0, 1, 13);

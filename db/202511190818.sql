CREATE DATABASE Cantina;

USE Cantina;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    matricula VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    saldo DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE gestores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria_id INT,
    imagem_url VARCHAR(255),
    estoque INT DEFAULT 0,
    status ENUM('disponivel', 'esgotado', 'inativo') DEFAULT 'disponivel',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM(
        'aguardando_pagamento',
        'pago',
        'em_preparo',
        'pronto',
        'retirado',
        'cancelado'
    ) DEFAULT 'aguardando_pagamento',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (aluno_id) REFERENCES usuarios(id)
);

CREATE TABLE itens_do_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_no_momento DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

CREATE TABLE Movimentacao_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tipo ENUM('Entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (pRoduto_id) REFERENCES Produtos(iD)
);

ALTER TABLE usuarios 
ADD COLUMN pin_parental VARCHAR(6) DEFAULT NULL,
ADD COLUMN limite_diario DECIMAL(10,2) DEFAULT 50.00,
ADD COLUMN limite_por_pedido DECIMAL(10,2) DEFAULT 20.00,
ADD COLUMN restricao_horario BOOLEAN DEFAULT 0,
ADD COLUMN aprovacao_pais BOOLEAN DEFAULT 0;

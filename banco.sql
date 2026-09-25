CREATE DATABASE IF NOT EXISTS caminho_livre DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE caminho_livre;

CREATE TABLE IF NOT EXISTS usuarios (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        nome VARCHAR(100) NOT NULL,
                                        email VARCHAR(100) NOT NULL UNIQUE,
                                        senha VARCHAR(255) NOT NULL,
                                        role ENUM('cliente', 'admin') DEFAULT 'cliente',
                                        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS enderecos (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         usuario_id INT NOT NULL,
                                         tipo VARCHAR(20) DEFAULT 'envio',
                                         logradouro VARCHAR(255) NOT NULL,
                                         numero VARCHAR(20) NOT NULL,
                                         complemento VARCHAR(100) NULL,
                                         bairro VARCHAR(100) NOT NULL,
                                         cidade VARCHAR(100) NOT NULL,
                                         estado VARCHAR(50) NOT NULL,
                                         cep VARCHAR(10) NOT NULL,
                                         FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS categorias (
                                          id INT AUTO_INCREMENT PRIMARY KEY,
                                          nome VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS produtos (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        categoria_id INT NOT NULL,
                                        nome VARCHAR(150) NOT NULL,
                                        descricao TEXT,
                                        preco DECIMAL(10, 2) NOT NULL,
                                        estoque INT NOT NULL DEFAULT 0,
                                        imagens JSON NOT NULL,
                                        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE IF NOT EXISTS carrinho (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        usuario_id INT NOT NULL,
                                        produto_id INT NOT NULL,
                                        quantidade INT NOT NULL DEFAULT 1,
                                        UNIQUE KEY (usuario_id, produto_id),
                                        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                                        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pedidos (
                                       id INT AUTO_INCREMENT PRIMARY KEY,
                                       usuario_id INT NOT NULL,
                                       total DECIMAL(10, 2) NOT NULL,
                                       endereco_envio_id INT NOT NULL,
                                       status ENUM('Processando', 'Em preparação', 'Enviado', 'Entregue') DEFAULT 'Processando',
                                       data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                       FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
                                       FOREIGN KEY (endereco_envio_id) REFERENCES enderecos(id)
);

CREATE TABLE IF NOT EXISTS itens_pedido (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            pedido_id INT NOT NULL,
                                            produto_id INT NOT NULL,
                                            quantidade INT NOT NULL,
                                            preco_unitario DECIMAL(10, 2) NOT NULL,
                                            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
                                            FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Inserção de categorias padrão
INSERT INTO categorias (id, nome) VALUES
                                      (1, 'Verão'), (2, 'Inverno'), (3, 'Infantil'), (4, 'Feminina'),
                                      (5, 'Relógios'), (6, 'Chapéus e Bonés'), (7, 'Joias'),
                                      (8, 'Tênis'), (9, 'Botas'), (10, 'Sandálias')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserção de usuário administrador padrão (Senha: admin123)
INSERT INTO usuarios (id, nome, email, senha, role) VALUES
    (1, 'Administrador', 'admin@caminholivre.com', '$2y$10$tH702b8d00B6zL.a8Ie7p.U0h0jL72Xv.LgH0i7j16u4zH.jM2q.W', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO enderecos (id, usuario_id, tipo, logradouro, numero, complemento, bairro, cidade, estado, cep) VALUES
    (1, 1, 'envio', 'Rua Central', '100', 'Sala 1', 'Centro', 'Rio de Janeiro', 'RJ', '20000000')
ON DUPLICATE KEY UPDATE usuario_id = VALUES(usuario_id);

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagens) VALUES

-- ==================== ROUPAS: VERÃO (Categoria 1) ====================
(1, 'Camiseta Masculina', 'Camiseta esportiva preta com caimento anatômico', 50.00, 30, '[{"url": "img/camiseta-masculina.webp"}]'),
(1, 'Regata Masculina', 'Regata básica de algodão confortável', 45.00, 25, '[{"url": "img/regata-masculina.webp"}]'),
(1, 'Bermuda Casual', 'Bermuda cargo preta com bolsos utilitários', 60.00, 20, '[{"url": "img/bermuda-casual.webp"}]'),
(1, 'Camisa Floral Oriental', 'Camisa manga curta leve com estampa floral oriental', 75.00, 15, '[{"url": "img/camisa-de-verao-floral.webp"}]'),
(1, 'Vestido Floral Verão', 'Vestido ciganinha leve com babados', 85.00, 18, '[{"url": "img/vestido-floral.webp"}]'),

-- ==================== ROUPAS: INVERNO (Categoria 2) ====================
(2, 'Jaqueta Puffer com Capuz', 'Jaqueta acolchoada térmica impermeável preta', 220.00, 15, '[{"url": "img/casaco-inverno.webp"}]'),
(2, 'Sobretudo Clássico', 'Casaco trench coat de lã com abotoamento frontal', 195.00, 10, '[{"url": "img/casaco-fashion.webp"}]'),
(2, 'Kit Moletons com Capuz', 'Trio de moletons básicos flanelados unissex', 140.00, 20, '[{"url": "img/moletom-inverno.webp"}]'),
(2, 'Casaco Moletom com Zíper', 'Jaqueta de moletom esportiva preta', 125.00, 18, '[{"url": "img/jaqueta-moletom.webp"}]'),
(2, 'Calça Moletom Street', 'Calça jogger preta com listra lateral branca', 89.90, 25, '[{"url": "img/calca-moletom.webp"}]'),
(2, 'Cachecol Xadrez de Inverno', 'Cachecol grosso em padrão xadrez tradicional', 49.90, 30, '[{"url": "img/cachecol-inverno.webp"}]'),
(2, 'Luvas Térmicas Touch Screen', 'Luvas quentes compatíveis com tela de celular', 35.00, 40, '[{"url": "img/luva-termica.webp"}]'),

-- ==================== ROUPAS: INFANTIL (Categoria 3) ====================
(3, 'Conjunto Infantil Raposa', 'Conjunto moletom com capuz e calça cinza', 79.90, 20, '[{"url": "img/conjunto-infantil.webp"}]'),
(3, 'Jaqueta Puffer Kids', 'Jaqueta acolchoada bicolor azul e vermelha', 110.00, 15, '[{"url": "img/jaqueta-infantil.webp"}]'),
(3, 'Kit Blusões Kids', 'Kit com 3 blusões básicos em cores neutras', 69.90, 22, '[{"url": "img/moletom-infantil.webp"}]'),
(3, 'Bermuda Infantil Básica', 'Bermuda ciclista infantil azul marinho', 39.90, 30, '[{"url": "img/short-infantil.webp"}]'),
(3, 'Vestido Infantil Renda Azul', 'Vestido delicado com bordados e laço', 89.90, 14, '[{"url": "img/vestido-infantil.webp"}]'),

-- ==================== ROUPAS: FEMININA (Categoria 4) ====================
(4, 'Blusa Feminina Bordada', 'Blusa clássica manga longa com gola bordada', 65.00, 20, '[{"url": "img/blusa-feminina.webp"}]'),
(4, 'Camisa Seda Floral Elegante', 'Camisa social estampada de botões', 95.00, 16, '[{"url": "img/blusa-elegante.webp"}]'),
(4, 'Saia Jeans com Botões', 'Saia jeans moderna com acabamento desfiado', 70.00, 18, '[{"url": "img/saia-jeans.webp"}]'),
(4, 'Saia Midi Plissada', 'Saia plissada elegante em tecido encorpado', 85.00, 15, '[{"url": "img/saia-plissada.webp"}]'),
(4, 'Calça Alfaiataria Feminina', 'Calça social com corte reto e caimento estruturado', 110.00, 18, '[{"url": "img/calca-social-feminina.webp"}]'),
(4, 'Macacão Canelado com Faixa', 'Macacão pantacourt vinho com decote V', 130.00, 12, '[{"url": "img/macacao-feminino.webp"}]'),
(4, 'Vestido Longo Tropical', 'Vestido longo ombro a ombro estampado amarelo', 145.00, 10, '[{"url": "img/vestido-longo.webp"}]'),

-- ==================== ACESSÓRIOS: RELÓGIOS (Categoria 5) ====================
(5, 'Smartwatch Champion', 'Relógio inteligente preto multifunções', 180.00, 20, '[{"url": "img/relogio-moderno.webp"}]'),
(5, 'Relógio Cronógrafo Couro', 'Relógio executivo preto com detalhes rose gold', 230.00, 10, '[{"url": "img/relogio-classico.webp"}]'),
(5, 'Relógio Dourado Luxo', 'Relógio analógico banhado a ouro com pulseira metálica', 260.00, 8, '[{"url": "img/relogio-luxo.webp"}]'),
(5, 'Relógio Minimalista Preto', 'Relógio casual ultrafino com pulseira em couro', 120.00, 15, '[{"url": "img/relogio-casual.webp"}]'),
(5, 'Relógio Digital Tático', 'Relógio militar resistente a choques e água', 95.00, 25, '[{"url": "img/relogio-digital.webp"}]'),
(5, 'Relógio Esportivo Digital LED', 'Relógio esportivo com display cyan de alta visibilidade', 89.90, 22, '[{"url": "img/relogio-esportivo.webp"}]'),

-- ==================== ACESSÓRIOS: CHAPÉUS E BONÉS (Categoria 6) ====================
(6, 'Chapéu Fedora com Faixa', 'Chapéu de palha estilo fedora com faixa de couro', 85.00, 15, '[{"url": "img/chapeu-social.webp"}]'),
(6, 'Chapéu de Praia Floppy', 'Chapéu de palha com aba larga e proteção UV', 75.00, 18, '[{"url": "img/chapeu-praia.webp"}]'),
(6, 'Chapéu Panamá Elegance', 'Chapéu clássico estruturado com fita marrom', 80.00, 12, '[{"url": "img/chapeu-elegante.webp"}]'),
(6, 'Boné Aba Curva Verde', 'Boné snapback confeccionado em sarja', 55.00, 30, '[{"url": "img/bone-casual.webp"}]'),
(6, 'Boné Esportivo Runner', 'Boné esportivo respirável de secagem rápida', 50.00, 25, '[{"url": "img/bone-esportivo.webp"}]'),
(6, 'Boné Trucker Vintage', 'Boné preto estruturado com patch frontal bordado', 60.00, 20, '[{"url": "img/bone-retro.webp"}]'),
(6, 'Gorro Térmico Listrado', 'Touca de lã forrada com fleece peluciado', 39.90, 35, '[{"url": "img/touca-termica.webp"}]'),

-- ==================== ACESSÓRIOS: JOIAS (Categoria 7) ====================
(6, 'Brinco Argola com Zircônias', 'Argola dourada cravejada com microcristais', 45.00, 25, '[{"url": "img/brinco-argola.webp"}]'),
(7, 'Brinco Retangular Bicolor', 'Argolas geométricas combinando dourado e prata', 49.90, 20, '[{"url": "img/brinco-feminino.webp"}]'),
(7, 'Gargantilha Coração em Prata', 'Colar delicado em prata 925 com pingente vazado', 80.00, 18, '[{"url": "img/pingente-coracao.webp"}]'),
(7, 'Corrente Masculina Cordão Baiano', 'Corrente torcida em aço cirúrgico prateado', 70.00, 20, '[{"url": "img/cordao-masculino.webp"}]'),
(7, 'Pulseira Riviera de Cristais', 'Pulseira banhada a ouro com pedras retangulares', 95.00, 15, '[{"url": "img/pulseira-luxo.webp"}]'),

-- ==================== CALÇADOS: TÊNIS (Categoria 8) ====================
(8, 'Tênis Casual Plataforma Branco', 'Sneaker branco com solado reto e costuras reforçadas', 130.00, 20, '[{"url": "img/tenis-fashion.webp"}]'),
(8, 'Tênis Lona Polo Classic', 'Tênis casual preto com detalhes em couro marrom', 145.00, 18, '[{"url": "img/tenis-casual.webp"}]'),
(8, 'Tênis de Corrida Gradient', 'Tênis performance em mesh respirável azul e branco', 165.00, 15, '[{"url": "img/tenis-corrida.webp"}]'),
(8, 'Tênis Esportivo Olimpak', 'Tênis amortecimento com solado translúcido azul', 150.00, 16, '[{"url": "img/tenis-esportivo.webp"}]'),
(8, 'Tênis Retro Runner Bege', 'Tênis camurça retrô com solado antiderrapante', 175.00, 12, '[{"url": "img/tenis-retro.webp"}]'),
(8, 'Tênis QIX Skate Classic', 'Tênis acolchoado camurça azul e preto para skate', 199.90, 10, '[{"url": "img/tenis-skate.webp"}]'),
(8, 'Tênis Cano Baixo Red & Black', 'Tênis estilo basquete em couro sintético preto e vermelho', 210.00, 14, '[{"url": "img/tenis-vermelho.webp"}]'),
(8, 'Tênis Slip-on Infantil Novopé', 'Tênis infantil sem cadarço com detalhes em rosa', 89.90, 20, '[{"url": "img/tenis-infantil.webp"}]'),

-- ==================== CALÇADOS: BOTAS (Categoria 9) ====================
(9, 'Coturno Fivelas e Salto', 'Bota cano médio com tiras, fivelas duplas e salto robusto', 215.00, 12, '[{"url": "img/bota-couro.webp"}]'),
(9, 'Coturno Tratorado Preto', 'Bota moderna solado plataforma em couro sintético', 180.00, 15, '[{"url": "img/bota-feminina.webp"}]'),
(9, 'Bota Montaria Salto Bloco', 'Bota de cano alto em couro nobre com zíper', 240.00, 8, '[{"url": "img/bota-montaria.webp"}]'),
(9, 'Bota Casual Couro Marrom', 'Botina urbana confortável com solado costurado', 190.00, 10, '[{"url": "img/bota-casual.webp"}]'),
(9, 'Bota de Trilha e Aventura', 'Bota de trekking antiderrapante com detalhes em camurça', 210.00, 14, '[{"url": "img/bota-trilha.webp"}]'),
(9, 'Bota Camurça Forrada Pelinhos', 'Bota cano médio invernal dobrável e forrada', 199.00, 12, '[{"url": "img/bota-inverno.webp"}]'),
(9, 'Bota Neve Tratorada Caramelo', 'Coturno quente forrado com amarração reforçada', 225.00, 10, '[{"url": "img/bota-inverno2.webp"}]'),
(9, 'Bota Alta Inverno com Fivelas', 'Bota cano longo em camurça marrom com pelo de carneiro', 235.00, 8, '[{"url": "img/bota-inverno3.webp"}]'),

-- ==================== CALÇADOS: SANDÁLIAS (Categoria 10) ====================
(10, 'Sandália Rasteira Couro Trançado', 'Sandália rasteira artesanal em tons de marrom', 69.90, 25, '[{"url": "img/sandalia-casual.webp"}]'),
(10, 'Sandália Anabela Confort', 'Sandália marrom ortopédica com palmilha macia', 89.90, 20, '[{"url": "img/sandalia-conforto.webp"}]'),
(10, 'Sandália Rasteira Pedrarias', 'Rasteirinha delicada com detalhes dourados', 75.00, 18, '[{"url": "img/sandalia-rasteira.webp"}]'),
(10, 'Sandália Salto Bloco Tiras', 'Sandália off-white com salto médio e amarração', 119.90, 15, '[{"url": "img/sandalia-salto.webp"}]'),
(10, 'Sandália Festa Tiras Strass', 'Sandália branca de salto grosso com tiras brilhantes', 139.90, 12, '[{"url": "img/sandalia-festa.webp"}]'),
(10, 'Sandália Plataforma Prata Amarração', 'Sandália salto alto prata com plataforma e tiras longas', 149.90, 10, '[{"url": "img/sandalia-plataforma.webp"}]');
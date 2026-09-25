<?php
require 'config.php'; // Inclui a conexão e a sessão

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado para adicionar itens ao carrinho.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];
$produto_nome = $dados['nome'] ?? '';
$quantidade = 1; // Quantidade padrão

if (empty($produto_nome)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Nome do produto não fornecido.']);
    exit();
}

try {
    // 1. Encontrar o ID do produto pelo nome
    $sql_produto = "SELECT id FROM produtos WHERE nome = ? LIMIT 1";
    $stmt_produto = $conn->prepare($sql_produto);
    $stmt_produto->bind_param("s", $produto_nome);
    $stmt_produto->execute();
    $result_produto = $stmt_produto->get_result();
    
    if ($result_produto->num_rows === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Produto não encontrado.']);
        exit();
    }
    $produto = $result_produto->fetch_assoc();
    $produto_id = $produto['id'];

    // 2. Inserir ou atualizar o item no carrinho (ON DUPLICATE KEY UPDATE)
    $sql_carrinho = "INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade)";
    $stmt_carrinho = $conn->prepare($sql_carrinho);
    $stmt_carrinho->bind_param("iii", $usuario_id, $produto_id, $quantidade);
    $stmt_carrinho->execute();

    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produto adicionado ao carrinho!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao adicionar produto ao carrinho.', 'detalhes' => $e->getMessage()]);
}

$stmt_produto->close();
$stmt_carrinho->close();
$conn->close();
?>
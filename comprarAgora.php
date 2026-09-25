<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado para finalizar a compra.']);
exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];
$endereco_id = $dados['endereco_id'] ?? null;

if (empty($endereco_id)) {
echo json_encode(['status' => 'erro', 'mensagem' => 'Endereço de entrega não fornecido.']);
exit();
}

$conn->begin_transaction();

try {
// 1. Obter itens do carrinho com preços reais do banco de dados e verificar estoque
$sql_carrinho = "SELECT c.produto_id, c.quantidade, p.nome, p.preco, p.estoque
FROM carrinho c
JOIN produtos p ON c.produto_id = p.id
WHERE c.usuario_id = ?";
$stmt_carrinho = $conn->prepare($sql_carrinho);
$stmt_carrinho->bind_param("i", $usuario_id);
$stmt_carrinho->execute();
$result_carrinho = $stmt_carrinho->get_result();

if ($result_carrinho->num_rows === 0) {
throw new Exception("Seu carrinho está vazio.");
}

$itens = [];
$total_calculado = 0.0;

while ($row = $result_carrinho->fetch_assoc()) {
if ($row['estoque'] < $row['quantidade']) {
throw new Exception("Estoque insuficiente para o produto: " . $row['nome']);
}
$total_calculado += ($row['preco'] * $row['quantidade']);
$itens[] = $row;
}
$stmt_carrinho->close();

// 2. Criar o pedido com o total calculado com segurança no servidor
$sql_pedido = "INSERT INTO pedidos (usuario_id, total, endereco_envio_id, status) VALUES (?, ?, ?, 'Processando')";
$stmt_pedido = $conn->prepare($sql_pedido);
$stmt_pedido->bind_param("idi", $usuario_id, $total_calculado, $endereco_id);
$stmt_pedido->execute();
$pedido_id = $conn->insert_id;
$stmt_pedido->close();

// 3. Mover itens para itens_pedido
$sql_itens = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
$stmt_itens = $conn->prepare($sql_itens);
foreach ($itens as $item) {
$stmt_itens->bind_param("iiid", $pedido_id, $item['produto_id'], $item['quantidade'], $item['preco']);
$stmt_itens->execute();
}
$stmt_itens->close();

// 4. Atualizar o estoque
$sql_update_estoque = "UPDATE produtos p JOIN carrinho c ON p.id = c.produto_id
SET p.estoque = p.estoque - c.quantidade
WHERE c.usuario_id = ?";
$stmt_update = $conn->prepare($sql_update_estoque);
$stmt_update->bind_param("i", $usuario_id);
$stmt_update->execute();
$stmt_update->close();

// 5. Limpar carrinho
$sql_limpar = "DELETE FROM carrinho WHERE usuario_id = ?";
$stmt_limpar = $conn->prepare($sql_limpar);
$stmt_limpar->bind_param("i", $usuario_id);
$stmt_limpar->execute();
$stmt_limpar->close();

$conn->commit();
echo json_encode([
'status' => 'sucesso',
'mensagem' => 'Compra realizada com sucesso! Pedido nº ' . $pedido_id
]);

} catch (Exception $e) {
$conn->rollback();
echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao processar a compra: ' . $e->getMessage()]);
}

$conn->close();
?>
<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Sessão expirada.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$carrinho_id = $dados['carrinho_id'] ?? 0;
$quantidade = $dados['quantidade'] ?? 1;
$usuario_id = $_SESSION['usuario_id'];

// Validação: a quantidade não pode ser menor que 1
if ($quantidade < 1) {
    $quantidade = 1;
}

if (empty($carrinho_id)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do item não fornecido.']);
    exit();
}

try {
    // A condição usuario_id garante que um usuário não possa alterar o carrinho de outro
    $sql = "UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantidade, $carrinho_id, $usuario_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Quantidade atualizada.']);
    } else {
        // Se nenhuma linha foi afetada, pode ser que a quantidade já era a mesma.
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Nenhuma alteração necessária.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar a quantidade.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
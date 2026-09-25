<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Sessão expirada.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$carrinho_id = $dados['carrinho_id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

if (empty($carrinho_id)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do item não fornecido.']);
    exit();
}

try {
    // A condição usuario_id garante que um usuário não possa remover o item de outro
    $sql = "DELETE FROM carrinho WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $carrinho_id, $usuario_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Item removido com sucesso.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Não foi possível remover o item.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao remover o item.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
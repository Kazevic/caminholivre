<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Sessão expirada.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $sql = "DELETE FROM carrinho WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();

    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Carrinho esvaziado com sucesso.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao esvaziar o carrinho.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
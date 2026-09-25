<?php
require 'config.php';
header('Content-Type: application/json');

if (($_SESSION['usuario_role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$pedido_id = $dados['pedido_id'] ?? 0;
$novo_status = $dados['status'] ?? '';

// Lista de status válidos
$status_validos = ['Processando', 'Em preparação', 'Enviado', 'Entregue'];

if (empty($pedido_id) || !in_array($novo_status, $status_validos)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos.']);
    exit();
}

try {
    $sql = "UPDATE pedidos SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $novo_status, $pedido_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Status do pedido atualizado.']);
    } else {
        echo json_encode(['status' => 'info', 'mensagem' => 'Nenhuma alteração foi feita.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar o status.']);
}

$stmt->close();
$conn->close();
?>
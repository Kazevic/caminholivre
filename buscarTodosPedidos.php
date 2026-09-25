<?php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php';
header('Content-Type: application/json');

if (($_SESSION['usuario_role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
    exit();
}

try {
    // Busca todos os pedidos e junta com o nome do usuário
    $sql = "SELECT p.id, p.total, p.status, u.nome AS nome_cliente,
                   DATE_FORMAT(p.data_pedido, '%d/%m/%Y') as data_formatada
            FROM pedidos p
            JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.data_pedido DESC";
            
    $result = $conn->query($sql);
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
    echo json_encode($pedidos);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao buscar pedidos.']);
}

$conn->close();
?>
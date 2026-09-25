<?php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado para ver seus pedidos.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

try {
    // Busca os pedidos do usuário, do mais recente para o mais antigo
    $sql = "SELECT id, total, status, DATE_FORMAT(data_pedido, '%d/%m/%Y %H:%i') as data_formatada
            FROM pedidos
            WHERE usuario_id = ?
            ORDER BY data_pedido DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }

    echo json_encode($pedidos);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao buscar seus pedidos.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
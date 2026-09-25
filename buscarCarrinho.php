<?php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Você precisa estar logado para ver seu carrinho.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $sql = "SELECT c.id AS carrinho_id, p.nome, p.preco, p.imagens, c.quantidade
            FROM carrinho c
            JOIN produtos p ON c.produto_id = p.id
            WHERE c.usuario_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $carrinho = [];
    while ($row = $result->fetch_assoc()) {
        $row['imagens'] = json_decode($row['imagens'], true);
        $carrinho[] = $row;
    }

    echo json_encode($carrinho);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao buscar o carrinho.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
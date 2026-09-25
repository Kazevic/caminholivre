<?php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php';
header('Content-Type: application/json');

// Só o admin pode acessar essa lista
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
    exit();
}

try {
    $sql = "SELECT id, nome, preco, imagens, estoque FROM produtos ORDER BY nome ASC";
    $result = $conn->query($sql);
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $row['imagens'] = json_decode($row['imagens'], true);
        $produtos[] = $row;
    }

    echo json_encode($produtos);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao buscar produtos.', 'detalhes' => $e->getMessage()]);
}

$conn->close();
?>
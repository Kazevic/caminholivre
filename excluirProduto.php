<?php
require 'config.php';
header('Content-Type: application/json');

// Apenas um admin pode excluir produtos
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$produto_id = $dados['produto_id'] ?? 0;

if (empty($produto_id)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do produto não fornecido.']);
    exit();
}

$conn->begin_transaction();

try {
    // 1. Encontrar o caminho da imagem antes de apagar o produto
    $sql_select = "SELECT imagens FROM produtos WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $produto_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $imagens = json_decode($row['imagens'], true);
        $caminho_imagem = $imagens[0]['url'] ?? null;

        // 2. Apagar a imagem do servidor, se ela existir
        if ($caminho_imagem && file_exists($caminho_imagem)) {
            unlink($caminho_imagem);
        }
    }
    $stmt_select->close();

    // 3. Apagar o produto do banco de dados
    $sql_delete = "DELETE FROM produtos WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $produto_id);
    $stmt_delete->execute();

    if ($stmt_delete->affected_rows > 0) {
        $conn->commit();
        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produto excluído com sucesso.']);
    } else {
        throw new Exception('Produto não encontrado ou já excluído.');
    }
    $stmt_delete->close();

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir o produto.', 'detalhes' => $e->getMessage()]);
}

$conn->close();
?>
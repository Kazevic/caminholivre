<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

require 'config.php';

try {
    $tipo = $_GET['tipo'] ?? 'categoria';
    $categoria_nome = $_GET['categoria_nome'] ?? null;
    $stmt = null;

    if ($tipo === 'categoria' && $categoria_nome) {
        $sql = "SELECT p.nome, p.preco, p.imagens FROM produtos p
                JOIN categorias c ON p.categoria_id = c.id
                WHERE c.nome COLLATE utf8mb4_general_ci = ? LIMIT 20";
        $stmt = $conn->prepare($sql);
$stmt->bind_param("s", $categoria_nome);

} else if ($tipo === 'mais_vendidos') {
$sql = "SELECT p.nome, p.preco, p.imagens, SUM(it.quantidade) AS total_vendido
FROM produtos p
JOIN itens_pedido it ON p.id = it.produto_id
GROUP BY p.id
ORDER BY total_vendido DESC
LIMIT 6";
$stmt = $conn->prepare($sql);

} else {
throw new Exception('Parâmetros de busca inválidos.');
}

$stmt->execute();
$result = $stmt->get_result();
$produtos = [];

if ($result->num_rows === 0 && $tipo === 'mais_vendidos') {
$fallback_sql = "SELECT nome, preco, imagens FROM produtos ORDER BY estoque DESC LIMIT 6";
$result = $conn->query($fallback_sql);
}

while ($row = $result->fetch_assoc()) {
$row['imagens'] = json_decode($row['imagens'], true);
$produtos[] = $row;
}

echo json_encode($produtos);

if ($stmt) $stmt->close();
$conn->close();

} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'erro' => 'Ocorreu um erro no servidor.',
'detalhes' => $e->getMessage()
]);
}
?>
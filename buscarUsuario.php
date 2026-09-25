<?php
   header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php'; // Inclui a conexão e o início da sessão
header('Content-Type: application/json');

// Verifica se tem um usuário logado na sessão
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Utilizador não autenticado.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

try {
    // Prepara e busca o usuário e o seu endereço de envio
    $sql = "SELECT u.nome, u.email, e.logradouro, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep, e.id AS endereco_id
            FROM usuarios u
            JOIN enderecos e ON u.id = e.usuario_id
            WHERE u.id = ? AND e.tipo = 'envio'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['erro' => 'Utilizador ou endereço de envio não encontrado no banco de dados.']);
    }

} catch (Exception $e) {
    echo json_encode(['erro' => 'Ocorreu um erro no servidor.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

$dados = json_decode(file_get_contents('php://input'), true);
$usuario_id = $_SESSION['usuario_id'];

// Validação básica dos campos
if (empty($dados['nome']) || empty($dados['email']) || empty($dados['cep'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Nome, email e CEP são obrigatórios.']);
    exit();
}

// Inicia uma transação para garantir que ambas as tabelas sejam atualizadas
$conn->begin_transaction();

try {
    // 1. Atualiza a tabela 'usuarios'
    // Verifica se uma nova senha foi fornecida
    if (!empty($dados['senha'])) {
        // Se sim, atualiza nome, email E a senha com hash
        $sql_usuario = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
        $stmt_usuario = $conn->prepare($sql_usuario);
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $stmt_usuario->bind_param("sssi", $dados['nome'], $dados['email'], $senha_hash, $usuario_id);
    } else {
        // Se não, atualiza apenas nome e email
        $sql_usuario = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
        $stmt_usuario = $conn->prepare($sql_usuario);
        $stmt_usuario->bind_param("ssi", $dados['nome'], $dados['email'], $usuario_id);
    }
    $stmt_usuario->execute();

    // 2. Atualiza a tabela 'enderecos'
    $sql_endereco = "UPDATE enderecos SET logradouro = ?, numero = ?, bairro = ?, cidade = ?, estado = ?, cep = ? WHERE usuario_id = ? AND tipo = 'envio'";
    $stmt_endereco = $conn->prepare($sql_endereco);
    $stmt_endereco->bind_param("ssssssi", $dados['logradouro'], $dados['numero'], $dados['bairro'], $dados['cidade'], $dados['estado'], $dados['cep'], $usuario_id);
    $stmt_endereco->execute();

    // Se tudo deu certo, confirma as alterações
    $conn->commit();

    // Atualiza o nome na sessão para refletir a mudança imediatamente no site
    $_SESSION['usuario_nome'] = $dados['nome'];

    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Perfil atualizado com sucesso!']);

} catch (Exception $e) {
    // Se algo deu errado, desfaz todas as alterações
    $conn->rollback();
    // Verifica se o erro foi de e-mail duplicado
    if ($conn->errno == 1062) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Este e-mail já está em uso por outra conta.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar o perfil.', 'detalhes' => $e->getMessage()]);
    }
}

$stmt_usuario->close();
$stmt_endereco->close();
$conn->close();
?>
<?php
require 'config.php'; // Inclui a conexão e inicia a sessão

header('Content-Type: application/json');

// Recebe os dados do formulário via POST
$dados = json_decode(file_get_contents('php://input'), true);

// Validação básica dos campos
if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha']) || empty($dados['cep'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
    exit();
}

// Inicia uma transação para garantir que ambos os inserts funcionem
$conn->begin_transaction();

try {
    // 1. Insere o usuário na tabela 'usuarios'
    $sql_usuario = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    // Criptografa a senha para segurança
    $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
    $stmt_usuario->bind_param("sss", $dados['nome'], $dados['email'], $senha_hash);
    $stmt_usuario->execute();
    
    // Pega o ID do usuário recém-criado
    $usuario_id = $conn->insert_id;

    // 2. Insere o endereço na tabela 'enderecos'
    $sql_endereco = "INSERT INTO enderecos (usuario_id, tipo, logradouro, numero, bairro, cidade, estado, cep) VALUES (?, 'envio', ?, ?, ?, ?, ?, ?)";
    $stmt_endereco = $conn->prepare($sql_endereco);
    $stmt_endereco->bind_param("issssss", $usuario_id, $dados['logradouro'], $dados['numero'], $dados['bairro'], $dados['cidade'], $dados['estado'], $dados['cep']);
    $stmt_endereco->execute();

    // Se tudo deu certo, confirma a transação
    $conn->commit();
    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Cadastro realizado com sucesso!']);

} catch (Exception $e) {
    // Se algo deu errado, desfaz a transação
    $conn->rollback();
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao realizar o cadastro.', 'detalhes' => $e->getMessage()]);
}

$stmt_usuario->close();
$stmt_endereco->close();
$conn->close();
?>
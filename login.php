<?php
require 'config.php'; // Inclui a conexão e inicia a sessão

header('Content-Type: application/json');
$dados = json_decode(file_get_contents('php://input'), true);

if (empty($dados['email']) || empty($dados['senha'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Email e senha são obrigatórios.']);
    exit();
}

try {
    $sql = "SELECT id, nome, senha, role FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dados['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        // Verifica se a senha fornecida corresponde à senha criptografada no banco
        if (password_verify($dados['senha'], $usuario['senha'])) {
            // Armazena os dados do usuário na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_role'] = $usuario['role']; // Armazena a role do usuário
            echo json_encode(['status' => 'sucesso', 'mensagem' => 'Login realizado com sucesso!']);
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário ou senha incorretos.']);
        }
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário ou senha incorretos.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Ocorreu um erro no servidor.', 'detalhes' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
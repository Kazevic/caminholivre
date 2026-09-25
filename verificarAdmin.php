<?php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php';
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'isAdmin' => false, 'mensagem' => 'Não autenticado.']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$isAdmin = false;
$categorias = [];

try {
    // Verifica se o usuário tem a role 'admin'
    $sql_user = "SELECT role FROM usuarios WHERE id = ? LIMIT 1";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $usuario_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    
    if ($user = $result_user->fetch_assoc()) {
        if ($user['role'] === 'admin') {
            $isAdmin = true;
            // Se for admin, busca as categorias
            $sql_cat = "SELECT id, nome FROM categorias ORDER BY nome ASC";
            $result_cat = $conn->query($sql_cat);
            while($cat = $result_cat->fetch_assoc()) {
                $categorias[] = $cat;
            }
        }
    }
    
    if ($isAdmin) {
        echo json_encode(['status' => 'sucesso', 'isAdmin' => true, 'categorias' => $categorias]);
    } else {
        echo json_encode(['status' => 'erro', 'isAdmin' => false, 'mensagem' => 'Acesso negado.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'isAdmin' => false, 'mensagem' => 'Erro no servidor.', 'detalhes' => $e->getMessage()]);
}

$conn->close();
?>
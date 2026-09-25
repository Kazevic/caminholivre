<?php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require 'config.php'; // Apenas inicia a sessão e as configurações
header('Content-Type: application/json');

$resposta = [
    'logado' => false,
    'nome' => '',
    'role' => ''
];

if (isset($_SESSION['usuario_id'])) {
    $resposta['logado'] = true;
    $resposta['nome'] = $_SESSION['usuario_nome'] ?? 'Usuário';
    $resposta['role'] = $_SESSION['usuario_role'] ?? 'cliente';
}

echo json_encode($resposta);
?>
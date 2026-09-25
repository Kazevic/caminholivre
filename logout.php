<?php
// Inicia a sessão
session_start();

// Remove todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão completamente
session_destroy();

// Redireciona o usuário para a página inicial
header("location: index.html");
exit;
?>
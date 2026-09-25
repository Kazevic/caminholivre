<?php
// Arquivo de exemplo de configuração
// Renomeie este arquivo para config.php e insira os dados do seu banco local

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Credenciais padrão (XAMPP / WAMP geralmente usam 'root' e senha vazia)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "caminho_livre";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
header('Content-Type: application/json');
http_response_code(500);
echo json_encode([
'status' => 'erro',
'mensagem' => 'Falha na conexão com o banco de dados.',
'detalhes' => $e->getMessage()
]);
exit();
}
?>
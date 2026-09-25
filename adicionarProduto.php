<?php
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado. Sessão não iniciada.']);
exit();
}

$sql_user = "SELECT role FROM usuarios WHERE id = ? LIMIT 1";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $_SESSION['usuario_id']);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado. Apenas administradores podem adicionar produtos.']);
exit();
}

if (empty($_POST['nome']) || empty($_POST['preco']) || empty($_POST['estoque']) || empty($_POST['categoria_id']) || empty($_FILES['imagem'])) {
echo json_encode(['status' => 'erro', 'mensagem' => 'Todos os campos são obrigatórios.']);
exit();
}

$target_dir = "uploads/";
if (!is_dir($target_dir)) {
mkdir($target_dir, 0755, true);
}

$ext = strtolower(pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION));
$allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allowed_exts)) {
echo json_encode(['status' => 'erro', 'mensagem' => 'Apenas arquivos JPG, PNG e WEBP são permitidos.']);
exit();
}

$check = getimagesize($_FILES["imagem"]["tmp_name"]);
if ($check === false) {
echo json_encode(['status' => 'erro', 'mensagem' => 'O arquivo enviado não é uma imagem válida.']);
exit();
}

$image_name = uniqid('prod_', true) . '.' . $ext;
$target_file = $target_dir . $image_name;

if (!move_uploaded_file($_FILES["imagem"]["tmp_name"], $target_file)) {
echo json_encode(['status' => 'erro', 'mensagem' => 'Ocorreu um erro ao fazer o upload da imagem.']);
exit();
}

try {
$sql = "INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id, imagens) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$imagens_json = json_encode([['url' => $target_file]]);

$stmt->bind_param("ssdiis", $_POST['nome'], $_POST['descricao'], $_POST['preco'], $_POST['estoque'], $_POST['categoria_id'], $imagens_json);
$stmt->execute();

echo json_encode(['status' => 'sucesso', 'mensagem' => 'Produto adicionado com sucesso!']);
$stmt->close();

} catch (Exception $e) {
if (file_exists($target_file)) {
unlink($target_file);
}
echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao salvar o produto no banco de dados.', 'detalhes' => $e->getMessage()]);
}

$stmt_user->close();
$conn->close();
?>
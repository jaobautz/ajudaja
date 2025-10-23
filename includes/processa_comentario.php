<?php
include_once 'session.php';
include 'config.php'; // Já inclui a BASE_URL
include 'autenticacao.php'; 

validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$comentario = trim($_POST['comentario'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if (!$pedido_id || empty($comentario)) {
    $_SESSION['erro'] = "O comentário não pode estar vazio.";
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
    exit;
}

$sql = "INSERT INTO comentarios (pedido_id, usuario_id, comentario) VALUES ($1, $2, $3)";
if (@pg_query($conn, "DEALLOCATE insert_comment")) {}
pg_prepare($conn, "insert_comment", $sql);
$result = pg_execute($conn, "insert_comment", array($pedido_id, $usuario_id, $comentario));

if (!$result) {
    $_SESSION['erro'] = "Ocorreu um erro ao postar seu comentário. Tente novamente.";
}

pg_close($conn);
// --- CORREÇÃO DO REDIRECIONAMENTO ---
header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
exit;
?>
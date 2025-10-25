<?php
require_once 'session.php';
include 'config.php';
include 'autenticacao.php';

// Valida o token CSRF
validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

$conversa_id = filter_input(INPUT_POST, 'conversa_id', FILTER_VALIDATE_INT);
$mensagem = trim($_POST['mensagem'] ?? '');
$remetente_id = $_SESSION['usuario_id'];

if (!$conversa_id || empty($mensagem)) {
    $_SESSION['erro'] = "Mensagem inválida.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

// 1. Verificar se o usuário atual tem permissão para enviar mensagem nesta conversa
$sql_check = "SELECT id FROM conversas WHERE id = $1 AND (usuario_criador_id = $2 OR usuario_voluntario_id = $3)";
pg_prepare($conn, "check_permissao_chat", $sql_check);
$result_check = pg_execute($conn, "check_permissao_chat", array($conversa_id, $remetente_id, $remetente_id));

if (!$result_check || pg_num_rows($result_check) == 0) {
    $_SESSION['erro'] = "Você não tem permissão para acessar este chat.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

// 2. Inserir a mensagem
$sql_insert = "INSERT INTO mensagens (conversa_id, remetente_id, mensagem) VALUES ($1, $2, $3)";
pg_prepare($conn, "insert_mensagem", $sql_insert);
$result_insert = pg_execute($conn, "insert_mensagem", array($conversa_id, $remetente_id, $mensagem));

if (!$result_insert) {
    $_SESSION['erro'] = "Ocorreu um erro ao enviar sua mensagem.";
}

pg_close($conn);

// Redireciona de volta para a conversa
header("Location: " . BASE_URL . "/pages/chat.php?conversa_id=$conversa_id");
exit;
?>
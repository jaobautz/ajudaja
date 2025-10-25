<?php
require_once 'session.php';
include 'config.php';
include 'autenticacao.php';

// Valida o token CSRF
validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$usuario_voluntario_id = $_SESSION['usuario_id']; // O usuário logado é o voluntário

if (!$pedido_id) {
    $_SESSION['erro'] = "Pedido inválido.";
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

// 1. Buscar o ID do dono do pedido
$sql_pedido = "SELECT usuario_id FROM pedidos WHERE id = $1";
pg_prepare($conn, "get_pedido_owner", $sql_pedido);
$result_pedido = pg_execute($conn, "get_pedido_owner", array($pedido_id));

if (!$result_pedido || pg_num_rows($result_pedido) == 0) {
    $_SESSION['erro'] = "Pedido não encontrado.";
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}
$usuario_criador_id = pg_fetch_result($result_pedido, 0, 'usuario_id');

// 2. Voluntário não pode iniciar chat consigo mesmo
if ($usuario_voluntario_id == $usuario_criador_id) {
    $_SESSION['erro'] = "Você não pode iniciar um chat sobre seu próprio pedido.";
    header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
    exit;
}

// 3. Verificar se a conversa já existe (UNIQUE constraint no DB)
$sql_check = "SELECT id FROM conversas WHERE pedido_id = $1 AND usuario_voluntario_id = $2";
pg_prepare($conn, "check_conversa", $sql_check);
$result_check = pg_execute($conn, "check_conversa", array($pedido_id, $usuario_voluntario_id));

if ($result_check && pg_num_rows($result_check) > 0) {
    // Conversa já existe, apenas redireciona para ela
    $conversa_id = pg_fetch_result($result_check, 0, 'id');
} else {
    // 4. Conversa não existe, vamos criar
    $sql_insert = "INSERT INTO conversas (pedido_id, usuario_criador_id, usuario_voluntario_id) 
                   VALUES ($1, $2, $3) RETURNING id";
    pg_prepare($conn, "insert_conversa", $sql_insert);
    $result_insert = pg_execute($conn, "insert_conversa", array($pedido_id, $usuario_criador_id, $usuario_voluntario_id));
    
    if (!$result_insert) {
        $_SESSION['erro'] = "Ocorreu um erro ao iniciar a conversa. Tente novamente.";
        header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
        exit;
    }
    $conversa_id = pg_fetch_result($result_insert, 0, 'id');
}

pg_close($conn);

// 5. Redireciona o usuário para a tela do chat
header("Location: " . BASE_URL . "/pages/chat.php?conversa_id=$conversa_id");
exit;
?>
<?php
session_start();
include 'config.php';
include 'autenticacao.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../pages/dashboard.php');
    exit;
}

$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$usuario_id = $_SESSION['usuario_id'];

if (!$pedido_id) {
    $_SESSION['erro'] = "ID de pedido inválido.";
    header('Location: ../pages/dashboard.php');
    exit;
}

$sql = "DELETE FROM pedidos WHERE id = $1 AND usuario_id = $2";
pg_prepare($conn, "delete_pedido", $sql);
$result = pg_execute($conn, "delete_pedido", array($pedido_id, $usuario_id));

if ($result) {
    if (pg_affected_rows($result) > 0) {
        $_SESSION['sucesso'] = "Pedido excluído com sucesso!";
    } else {
        $_SESSION['erro'] = "Não foi possível excluir o pedido. Ele pode já ter sido removido ou você não tem permissão.";
    }
} else {
    $_SESSION['erro'] = "Ocorreu um erro ao tentar excluir o pedido: " . pg_last_error($conn);
}

pg_close($conn);
header('Location: ../pages/dashboard.php');
exit;
?>
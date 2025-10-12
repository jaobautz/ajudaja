<?php
session_start();
include 'config.php';
include 'autenticacao.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../pages/dashboard.php');
    exit;
}

$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

// Validações (exemplo simples)
if (!$pedido_id || empty($titulo) || empty($descricao)) {
    $_SESSION['erro'] = "Todos os campos são obrigatórios.";
    header("Location: ../pages/editar_pedido.php?id=$pedido_id");
    exit;
}

$sql = "UPDATE pedidos SET titulo = $1, descricao = $2, urgencia = $3, categoria = $4 WHERE id = $5 AND usuario_id = $6";
pg_prepare($conn, "update_pedido", $sql);
$result = pg_execute($conn, "update_pedido", array(
    $titulo,
    $descricao,
    $urgencia,
    $categoria,
    $pedido_id,
    $usuario_id
));

if ($result) {
    if (pg_affected_rows($result) > 0) {
        $_SESSION['sucesso'] = 'Pedido atualizado com sucesso!';
    }
    // Redireciona mesmo se nada mudou para o usuário ver o resultado
    header("Location: ../pages/pedido_detalhe.php?id=$pedido_id");
} else {
    $_SESSION['erro'] = 'Erro ao executar a atualização: ' . pg_last_error($conn);
    header("Location: ../pages/editar_pedido.php?id=$pedido_id");
}

pg_close($conn);
exit;
?>
<?php
include_once 'session.php';
include 'config.php'; // Já inclui a BASE_URL
include 'autenticacao.php';

validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

// (Falta a validação robusta aqui, podemos adicionar depois)
if (!$pedido_id || empty($titulo) || empty($descricao)) {
    $_SESSION['erro'] = "Todos os campos são obrigatórios.";
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id");
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
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
} else {
    $_SESSION['erro'] = 'Erro ao executar a atualização: ' . pg_last_error($conn);
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id");
}

pg_close($conn);
exit;
?>
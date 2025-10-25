<?php
require_once 'session.php'; // Alterado de session_start()
include 'config.php';

// TAREFA 2: Validar o token CSRF
validar_post_request();

if (isset($_SESSION['usuario_id']) && isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Valida o status para aceitar apenas valores permitidos
    if ($status !== 'Aberto' && $status !== 'Concluído') {
        echo "Erro: Status inválido.";
        exit;
    }
    
    $sql = "UPDATE pedidos SET status = $1 WHERE id = $2 AND usuario_id = $3";
    
    if (!pg_prepare($conn, "update_status", $sql)) {
        echo "Erro na preparação da query: " . pg_last_error($conn);
        exit;
    }
    
    $result = pg_execute($conn, "update_status", array($status, $id, $usuario_id));

    if ($result && pg_affected_rows($result) > 0) {
        echo "Status atualizado!";
    } else {
        echo "Erro: Você não tem permissão para alterar este pedido ou o pedido não existe.";
    }
    pg_close($conn);
} else {
    echo "Erro: Acesso negado ou dados incompletos.";
}
?>
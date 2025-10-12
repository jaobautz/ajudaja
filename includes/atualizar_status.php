<?php
session_start();
include 'config.php';
// Não precisa do autenticacao.php aqui pois a query já checa o usuario_id da sessão

if (isset($_SESSION['usuario_id']) && isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $usuario_id = $_SESSION['usuario_id'];
    
    $sql = "UPDATE pedidos SET status = $1 WHERE id = $2 AND usuario_id = $3";
    
    // Prepara a query uma única vez
    if (!pg_prepare($conn, "update_status", $sql)) {
        // Lida com o erro se a preparação falhar
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
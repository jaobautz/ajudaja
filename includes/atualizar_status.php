<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); // Forbidden
    echo "Erro: Acesso negado.";
    exit;
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // ATUALIZA o status APENAS SE o pedido pertencer ao usuário logado
    $sql = "UPDATE pedidos SET status = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $id, $usuario_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "Status atualizado!";
        } else {
            // Não afetou linhas, significa que o pedido não pertence ao usuário ou não existe
            http_response_code(403);
            echo "Erro: Você não tem permissão para alterar este pedido ou o pedido não existe.";
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo "Erro no servidor: " . $stmt->error;
    }
    $stmt->close();
} else {
    http_response_code(400); // Bad Request
    echo "Erro: Dados incompletos.";
}
$conn->close();
?>
<?php
require_once 'session.php'; 
require_once 'config.php'; 
require_once 'autenticacao.php'; 

// --- ATENÇÃO: Habilitar display_errors temporariamente AQUI se config.php não estiver funcionando ---
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// --- FIM DA ATENÇÃO ---

validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$comentario = trim($_POST['comentario'] ?? '');
$usuario_id = $_SESSION['usuario_id'];
$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT);
if ($parent_id <= 0) {
    $parent_id = null; 
}

if (!$pedido_id || empty($comentario)) {
    $_SESSION['erro'] = "O comentário não pode estar vazio.";
    header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
    exit;
}

if ($parent_id !== null) {
    $sql_check_parent = "SELECT id FROM comentarios WHERE id = $1 AND pedido_id = $2";
    if (!@pg_prepare($conn, "check_parent_comment", $sql_check_parent)) { die("Erro ao preparar check_parent_comment: " . pg_last_error($conn)); }
    $result_check_parent = pg_execute($conn, "check_parent_comment", array($parent_id, $pedido_id));
    if (!$result_check_parent || pg_num_rows($result_check_parent) == 0) {
        $_SESSION['erro'] = "Comentário pai inválido.";
        header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
        exit;
    }
}

// --- DEBUG: Verificar os valores antes de inserir ---
// echo "Valores para Inserir: <br>";
// var_dump($pedido_id, $usuario_id, $parent_id, $comentario);
// echo "<hr>";
// --- FIM DEBUG ---


$sql = "INSERT INTO comentarios (pedido_id, usuario_id, parent_id, comentario) VALUES ($1, $2, $3, $4)";
if (@pg_query($conn, "DEALLOCATE insert_comment")) {}
if (!@pg_prepare($conn, "insert_comment", $sql)) { die("Erro ao preparar insert_comment: " . pg_last_error($conn)); }

$result = pg_execute($conn, "insert_comment", array($pedido_id, $usuario_id, $parent_id, $comentario));

if (!$result) {
    // --- DEBUG: Mostrar o erro específico do PostgreSQL ---
    $db_error = pg_last_error($conn);
    $_SESSION['erro'] = "Ocorreu um erro ao postar seu comentário. Detalhes: " . htmlspecialchars($db_error);
    // echo "Erro ao executar: " . htmlspecialchars($db_error); // Descomente para ver o erro imediatamente
    // exit; // Descomente para parar aqui e ver o erro
    // --- FIM DEBUG ---
} else {
    unset($_SESSION['erro']); 
}

pg_close($conn);

$redirect_hash = ($parent_id !== null) ? "#comentario-" . $parent_id : "";
header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id" . $redirect_hash);
exit;
?>
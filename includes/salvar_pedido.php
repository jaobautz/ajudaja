<?php
session_start();
include 'config.php';
include 'autenticacao.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../pages/dashboard.php');
    exit;
}

$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = trim($_POST['categoria'] ?? '');
$whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');

// Adicione aqui sua lógica de validação de dados, se necessário

$sql = "INSERT INTO pedidos (usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id";
pg_prepare($conn, "insert_pedido", $sql);
$result = pg_execute($conn, "insert_pedido", array(
    $_SESSION['usuario_id'],
    $titulo,
    $descricao,
    $urgencia,
    $categoria,
    $whatsapp
));

if ($result) {
    $new_id = pg_fetch_result($result, 0, 'id');
    $_SESSION['sucesso'] = 'Pedido cadastrado com sucesso! ID: ' . $new_id;
    header('Location: ../pages/index.php');
} else {
    $_SESSION['erro'] = 'Erro ao salvar o pedido: ' . pg_last_error($conn);
    header('Location: ../pages/cadastrar.php');
}

pg_close($conn);
exit;
?>
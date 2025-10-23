<?php
include_once 'session.php';
include 'config.php'; // Já inclui a BASE_URL
include 'autenticacao.php';
include 'validacao.php'; 

validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

// Coleta e validação (TAREFA 4)
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = trim($_POST['categoria'] ?? '');
$whatsapp = $_POST['whatsapp'] ?? '';
$erros = [];
$erro_titulo = validar_campo($titulo, ['obrigatorio', 'min:5', 'max:255']);
if ($erro_titulo) $erros['titulo'] = $erro_titulo;
$erro_descricao = validar_campo($descricao, ['obrigatorio', 'min:20']);
if ($erro_descricao) $erros['descricao'] = $erro_descricao;
$erro_urgencia = validar_campo($urgencia, ['obrigatorio', 'in:Urgente,Pode Esperar,Daqui a uma Semana']);
if ($erro_urgencia) $erros['urgencia'] = $erro_urgencia;
$erro_categoria = validar_campo($categoria, ['obrigatorio', 'in:Cesta Básica,Carona,Apoio Emocional,Doação de Itens,Serviços Voluntários,Outros']);
if ($erro_categoria) $erros['categoria'] = $erro_categoria;
$erro_whatsapp = validar_campo($whatsapp, ['obrigatorio', 'whatsapp']);
if ($erro_whatsapp) $erros['whatsapp'] = $erro_whatsapp;

if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros;
    $_SESSION['old_data'] = $_POST;
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/cadastrar.php');
    exit;
}

$whatsapp_limpo = preg_replace('/[^0-9]/', '', $whatsapp);

$sql = "INSERT INTO pedidos (usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id";
pg_prepare($conn, "insert_pedido", $sql);
$result = pg_execute($conn, "insert_pedido", array(
    $_SESSION['usuario_id'],
    $titulo,
    $descricao,
    $urgencia,
    $categoria,
    $whatsapp_limpo
));

if ($result) {
    $new_id = pg_fetch_result($result, 0, 'id');
    $_SESSION['sucesso'] = 'Pedido cadastrado com sucesso! ID: ' . $new_id;
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/index.php');
} else {
    $_SESSION['erro'] = 'Erro ao salvar o pedido: ' . pg_last_error($conn);
    $_SESSION['old_data'] = $_POST;
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/cadastrar.php');
}

pg_close($conn);
exit;
?>
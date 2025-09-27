<?php
session_start();
include '../includes/config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro'] = "Acesso negado. Faça login para continuar.";
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['erro'] = 'Método inválido!';
    header('Location: ../pages/cadastrar.php');
    exit;
}

$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = trim($_POST['categoria'] ?? '');
$whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');

$erros = [];
if (empty($titulo)) $erros[] = 'Título é obrigatório.';
if (empty($descricao)) $erros[] = 'Descrição é obrigatória.';

$urgencias_validas = ['Urgente', 'Pode Esperar', 'Daqui a uma Semana'];
if (empty($urgencia) || !in_array($urgencia, $urgencias_validas)) $erros[] = 'Selecione uma urgência válida.';

$categorias_validas = ['Cesta Básica', 'Carona', 'Apoio Emocional', 'Doação de Itens', 'Serviços Voluntários', 'Outros'];
if (empty($categoria) || !in_array($categoria, $categorias_validas)) $erros[] = 'Selecione uma categoria válida.';

if (empty($whatsapp) || !preg_match('/^\d{10,11}$/', $whatsapp)) $erros[] = 'WhatsApp inválido! Use apenas números, incluindo o DDD (10 ou 11 dígitos).';

if (!empty($erros)) {
    $_SESSION['erro'] = implode('<br>', $erros);
    header('Location: ../pages/cadastrar.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "INSERT INTO pedidos (usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['erro'] = 'Erro na preparação da query: ' . $conn->error;
    header('Location: ../pages/cadastrar.php');
    exit;
}

$stmt->bind_param("isssss", $usuario_id, $titulo, $descricao, $urgencia, $categoria, $whatsapp);

if ($stmt->execute()) {
    $_SESSION['sucesso'] = 'Pedido cadastrado com sucesso!';
    header('Location: ../pages/dashboard.php');
    exit;
} else {
    $_SESSION['erro'] = 'Erro ao salvar: ' . $stmt->error;
    header('Location: ../pages/cadastrar.php');
    exit;
}

$stmt->close();
$conn->close();
?>
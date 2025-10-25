<?php
require_once 'session.php';
include 'config.php';
include 'autenticacao.php';
require_once 'validacao.php';

// Valida o token CSRF
validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ' . BASE_URL . '/pages/perfil.php');
    exit;
}

// Coleta os dados
$usuario_id = $_SESSION['usuario_id'];
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

// Validação dos campos
$erros = [];

$erro_nome = validar_campo($nome, ['obrigatorio', 'min:3', 'max:100']);
if ($erro_nome) $erros['perfil_nome'] = $erro_nome; // Usamos prefixo para não conflitar com form de senha

$erro_email = validar_campo($email, ['obrigatorio', 'email']);
if ($erro_email) $erros['perfil_email'] = $erro_email;

$erro_telefone = validar_campo($telefone, ['obrigatorio', 'telefone']);
if ($erro_telefone) $erros['perfil_telefone'] = $erro_telefone;

// Se houver erros de validação, redireciona de volta
if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros;
    $_SESSION['old_data'] = $_POST;
    header('Location: ' . BASE_URL . '/pages/perfil.php');
    exit;
}

// Verifica se o NOVO email já está em uso por OUTRO usuário
$sql_check = "SELECT id FROM usuarios WHERE email = $1 AND id != $2";
pg_prepare($conn, "check_email_perfil", $sql_check);
$result_check = pg_execute($conn, "check_email_perfil", array($email, $usuario_id));

if ($result_check && pg_num_rows($result_check) > 0) {
    $_SESSION['erro'] = "Este email já está sendo usado por outra conta.";
    $_SESSION['old_data'] = $_POST;
    header('Location: ' . BASE_URL . '/pages/perfil.php');
    exit;
}

// Tudo certo, vamos atualizar o banco
$telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
$sql_update = "UPDATE usuarios SET nome = $1, email = $2, telefone = $3 WHERE id = $4";
pg_prepare($conn, "update_perfil", $sql_update);
$result_update = pg_execute($conn, "update_perfil", array($nome, $email, $telefone_limpo, $usuario_id));

if ($result_update) {
    // Atualiza o nome na sessão também
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['sucesso'] = "Perfil atualizado com sucesso!";
} else {
    $_SESSION['erro'] = "Ocorreu um erro ao atualizar seu perfil. Tente novamente.";
}

pg_close($conn);
header('Location: ' . BASE_URL . '/pages/perfil.php');
exit;
?>
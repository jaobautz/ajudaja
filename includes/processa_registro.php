<?php
require_once 'session.php';
include 'config.php';
require_once 'validacao.php'; 

// Coleta os dados
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$senha_confirm = trim($_POST['senha_confirm'] ?? '');

// --- APERFEIÇOAMENTO 2: Validação Robusta com Senha Forte ---
$erros = [];

$erro_nome = validar_campo($nome, ['obrigatorio', 'min:3', 'max:100']);
if ($erro_nome) $erros['nome'] = $erro_nome;

$erro_email = validar_campo($email, ['obrigatorio', 'email']);
if ($erro_email) $erros['email'] = $erro_email;

$erro_telefone = validar_campo($telefone, ['obrigatorio', 'telefone']);
if ($erro_telefone) $erros['telefone'] = $erro_telefone;

// Aplicando as novas regras de senha
$erro_senha = validar_campo($senha, ['obrigatorio', 'min:8', 'senha_forte']);
if ($erro_senha) $erros['senha'] = $erro_senha;

// Valida a confirmação da senha
$erro_senha_confirm = validar_campo($senha_confirm, ['obrigatorio', 'confirma:senha']);
if ($erro_senha_confirm) $erros['senha_confirm'] = $erro_senha_confirm;


// Se houver erros, redireciona de volta com os erros
if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros;
    $_SESSION['old_data'] = $_POST; 
    header('Location: ' . BASE_URL . '/pages/registrar.php');
    exit;
}
// --- FIM DA VALIDAÇÃO ---

// Verifica se o email já existe
$sql_check = "SELECT id FROM usuarios WHERE email = $1";
pg_prepare($conn, "check_email", $sql_check);
$result_check = pg_execute($conn, "check_email", array($email));

if ($result_check && pg_num_rows($result_check) > 0) {
    $_SESSION['erro'] = "Este email já está cadastrado.";
    $_SESSION['old_data'] = $_POST;
    header('Location: ' . BASE_URL . '/pages/registrar.php');
    exit;
}

// Criptografa a senha e limpa o telefone
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);

$sql_insert = "INSERT INTO usuarios (nome, email, senha, telefone) VALUES ($1, $2, $3, $4)";
pg_prepare($conn, "insert_user", $sql_insert);
$result_insert = pg_execute($conn, "insert_user", array($nome, $email, $senha_hash, $telefone_limpo));

if ($result_insert) {
    $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Faça o login.";
    header('Location: ' . BASE_URL . '/pages/login.php');
} else {
    $_SESSION['erro'] = "Erro ao cadastrar. Tente novamente.";
    header('Location: ' . BASE_URL . '/pages/registrar.php');
}

pg_close($conn);
?>
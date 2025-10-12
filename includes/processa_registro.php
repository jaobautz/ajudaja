<?php
session_start();
include 'config.php';

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($nome) || empty($email) || empty($senha)) {
    $_SESSION['erro'] = "Todos os campos são obrigatórios.";
    header('Location: ../pages/registrar.php');
    exit;
}

// Verifica se o email já existe
$sql_check = "SELECT id FROM usuarios WHERE email = $1";
pg_prepare($conn, "check_email", $sql_check);
$result_check = pg_execute($conn, "check_email", array($email));

if ($result_check && pg_num_rows($result_check) > 0) {
    $_SESSION['erro'] = "Este email já está cadastrado.";
    header('Location: ../pages/registrar.php');
    exit;
}

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere o novo usuário
$sql_insert = "INSERT INTO usuarios (nome, email, senha) VALUES ($1, $2, $3)";
pg_prepare($conn, "insert_user", $sql_insert);
$result_insert = pg_execute($conn, "insert_user", array($nome, $email, $senha_hash));

if ($result_insert) {
    $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Faça o login.";
    header('Location: ../pages/login.php');
} else {
    $_SESSION['erro'] = "Erro ao cadastrar. Tente novamente.";
    header('Location: ../pages/registrar.php');
}

pg_close($conn);
?>
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

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro'] = "Formato de email inválido.";
    header('Location: ../pages/registrar.php');
    exit;
}

// Verifica se o email já existe
$sql = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['erro'] = "Este email já está cadastrado.";
    header('Location: ../pages/registrar.php');
    exit;
}
$stmt->close();

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere o novo usuário no banco de dados
$sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nome, $email, $senha_hash);

if ($stmt->execute()) {
    $_SESSION['sucesso'] = "Cadastro realizado com sucesso! Faça o login.";
    header('Location: ../pages/login.php');
} else {
    $_SESSION['erro'] = "Erro ao cadastrar. Tente novamente.";
    header('Location: ../pages/registrar.php');
}
$stmt->close();
$conn->close();
?>
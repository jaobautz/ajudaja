<?php
session_start();
include 'config.php';

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    $_SESSION['erro'] = "Email e senha são obrigatórios.";
    header('Location: ../pages/login.php');
    exit;
}

$sql = "SELECT id, nome, senha FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
    
    // Verifica a senha
    if (password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: ../pages/dashboard.php');
        exit;
    }
}

// Se chegou até aqui, o login falhou
$_SESSION['erro'] = "Email ou senha inválidos.";
header('Location: ../pages/login.php');
exit;

?>
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

$sql = "SELECT id, nome, senha FROM usuarios WHERE email = $1";
pg_prepare($conn, "login_user", $sql);
$result = pg_execute($conn, "login_user", array($email));

if ($result && pg_num_rows($result) === 1) {
    $usuario = pg_fetch_assoc($result);
    
    if (password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        pg_close($conn);
        header('Location: ../pages/dashboard.php');
        exit;
    }
}

$_SESSION['erro'] = "Email ou senha inválidos.";
pg_close($conn);
header('Location: ../pages/login.php');
exit;
?>
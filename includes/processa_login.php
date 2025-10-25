<?php
require_once 'session.php'; // Usa require_once
require_once 'config.php'; // Usa require_once

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    $_SESSION['erro'] = "Email e senha são obrigatórios.";
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

$sql = "SELECT id, nome, senha FROM usuarios WHERE email = $1";
if (!@pg_prepare($conn, "login_user", $sql)) {
     // Em produção, logar o erro e mostrar mensagem genérica
     error_log("Falha ao preparar a query login_user: " . pg_last_error($conn));
     $_SESSION['erro'] = "Ocorreu um erro interno. Tente novamente.";
     header('Location: ' . BASE_URL . '/pages/login.php');
     exit;
}

$result = @pg_execute($conn, "login_user", array($email));

if (!$result) {
    // Em produção, logar o erro e mostrar mensagem genérica
    error_log("Falha ao executar a query login_user: " . pg_last_error($conn));
    $_SESSION['erro'] = "Ocorreu um erro interno. Tente novamente.";
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

if (pg_num_rows($result) === 1) {
    $usuario = pg_fetch_assoc($result);
    
    if (password_verify($senha, $usuario['senha'])) {
        // 1. Define quem o usuário é na sessão atual.
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        
        // 2. Regenera o ID da sessão DEPOIS de definir os dados.
        session_regenerate_id(true); 
        
        pg_close($conn);
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

// Se chegou aqui, email não encontrado ou senha incorreta
$_SESSION['erro'] = "Email ou senha inválidos.";
pg_close($conn);
header('Location: '. BASE_URL . '/pages/login.php');
exit;
?>
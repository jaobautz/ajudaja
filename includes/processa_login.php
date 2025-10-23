<?php
include_once 'session.php';
include 'config.php'; // Já inclui a BASE_URL

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    $_SESSION['erro'] = "Email e senha são obrigatórios.";
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

$sql = "SELECT id, nome, senha FROM usuarios WHERE email = $1";
pg_prepare($conn, "login_user", $sql);
$result = pg_execute($conn, "login_user", array($email));

if ($result && pg_num_rows($result) === 1) {
    $usuario = pg_fetch_assoc($result);
    
    if (password_verify($senha, $usuario['senha'])) {
        
        // --- CORREÇÃO DO BUG DE LOGIN DUPLO ---
        // 1. PRIMEIRO, define quem o usuário é na sessão atual.
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        
        // 2. DEPOIS, regenera o ID. Os dados da sessão são mantidos.
        session_regenerate_id(true); 
        // --- FIM DA CORREÇÃO ---
        
        pg_close($conn);
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

$_SESSION['erro'] = "Email ou senha inválidos.";
pg_close($conn);
header('Location: '. BASE_URL . '/pages/login.php');
exit;
?>
<?php
// Este script verifica se o usuário está logado.
// Deve ser incluído no topo das páginas protegidas.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro'] = "Você precisa fazer login para acessar esta página.";
    header('Location: ../pages/login.php');
    exit;
}
?>
<?php
// Este script verifica se o usuário está logado.
// Deve ser incluído no topo das páginas protegidas.

require_once 'session.php';
require_once 'config.php'; // Inclui o config para pegar a BASE_URL

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['erro'] = "Você precisa fazer login para acessar esta página.";
    
    // --- CORREÇÃO DO REDIRECIONAMENTO ---
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}
?>
<?php
// =======================================================
// CONFIGURAÇÃO CENTRAL DE SESSÃO PARA PRODUÇÃO
// =======================================================

$cookie_params = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', 
    'secure' => getenv('APP_ENV') === 'production', 
    'httponly' => true,
    'samesite' => 'Lax'
];
session_set_cookie_params($cookie_params);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function gerar_token_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validar_token_csrf($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

function validar_post_request() {
    // Inclui a config SÓ SE precisar redirecionar
    if (!isset($_POST['csrf_token']) || !validar_token_csrf($_POST['csrf_token'])) {
        include_once 'config.php'; // Inclui o config para pegar a BASE_URL
        
        error_log("Falha na validação do token CSRF.");
        $_SESSION['erro'] = 'Ação inválida ou sua sessão expirou. Por favor, tente novamente.';
        
        // --- CORREÇÃO DO REDIRECIONAMENTO ---
        $redirect_url = isset($_SESSION['usuario_id']) ? BASE_URL . '/pages/dashboard.php' : BASE_URL . '/pages/login.php';
        header('Location: ' . $redirect_url);
        exit;
    }
}
?>
<?php
// =======================================================
// ARQUIVO DE CONFIGURAÇÃO (MODO DE DEBUG FORÇADO)
// =======================================================

// --- DEBUG FORÇADO: Força a exibição de erros para encontrar a "tela branca" ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// -------------------------------------------------------------------------

// --- CONFIGURAÇÃO OBRIGATÓRIA (1/2): URL BASE ---
// (Verifique se 'ajudajaa' está correto)
define('BASE_URL', 'http://localhost/ajudajaa');

// --- CONFIGURAÇÃO OBRIGATÓRIA (2/2): BANCO DE DADOS ---
// (Verifique se 'ajudaja' e 'root' estão corretos)
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'ajudaja'; 
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'root'; 

// --- Conexão ---
$conn_str = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
$conn = pg_connect($conn_str);

if (!$conn) {
    // --- Força a exibição do erro de conexão ---
    $error_message = pg_last_error();
    die("Erro Crítico: Não foi possível conectar ao banco de dados PostgreSQL.<br>Detalhes: " . htmlspecialchars($error_message) . "<br>Verifique suas configurações em includes/config.php (dbname, user, password).");
}
?>
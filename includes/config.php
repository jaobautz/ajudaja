<?php
// =======================================================
// ARQUIVO DE CONFIGURAÇÃO (Adaptado para Debug Local)
// =======================================================

// --- CORREÇÃO: Voltando a URL base para 'ajudajaa' ---
define('BASE_URL', 'http://localhost/ajudajaa'); // Corrigido para dois 'a's

// --- Conexão Banco de Dados ---
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
// --- ATENÇÃO: Verifique se o nome do seu banco é 'ajudaja' ou 'ajudajaa' ---
$dbname = getenv('DB_NAME') ?: 'ajudaja'; // MANTIDO 'ajudaja' aqui, mas confirme o nome REAL do seu banco
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'root'; // Sua senha local

// --- CONFIGURAÇÃO DE ERROS PARA DEBUG LOCAL ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ini_set('log_errors', 1); // Descomente em produção
// ini_set('error_log', '/caminho/para/seu/log/php_errors.log'); // Defina em produção

// String de conexão
$conn_str = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
$conn = pg_connect($conn_str);

if (!$conn) {
    $error_message = pg_last_error();
    die("Erro Crítico: Não foi possível conectar ao banco de dados PostgreSQL.<br>Detalhes: " . htmlspecialchars($error_message) . "<br>Verifique suas configurações em includes/config.php (dbname: '{$dbname}', user: '{$user}', password).");
}
?>
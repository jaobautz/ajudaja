<?php
// =======================================================
// ARQUIVO DE CONFIGURAÇÃO PARA PRODUÇÃO (POSTGRESQL)
// =======================================================

// --- CORREÇÃO: Voltando a URL base e o nome do banco para 'ajudaja' ---
define('BASE_URL', 'http://localhost/ajudaja');

// --- LEITURA DAS VARIÁVEIS DE AMBIENTE ---
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'ajudaja'; // Corrigido
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'root'; 

// --- CONFIGURAÇÃO DE ERROS PARA PRODUÇÃO ---
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/caminho/para/seu/log/php_errors.log'); 
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// String de conexão para o PostgreSQL
$conn_str = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

$conn = pg_connect($conn_str);

if (!$conn) {
    error_log("Erro Fatal: Não foi possível conectar ao banco de dados PostgreSQL. DBName: {$dbname}, User: {$user}");
    die("Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.");
}
?>
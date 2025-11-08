<?php
define('BASE_URL', 'http://localhost/ajudajaa'); 

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'ajudaja'; 
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'root'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn_str = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
$conn = pg_connect($conn_str);

if (!$conn) {
    $error_message = pg_last_error();
    die("Erro Crítico: Não foi possível conectar ao banco de dados PostgreSQL.<br>Detalhes: " . htmlspecialchars($error_message) . "<br>Verifique suas configurações em includes/config.php (dbname: '{$dbname}', user: '{$user}', password).");
}
?>
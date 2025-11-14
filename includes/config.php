<?php

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'ajudaja'; 
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'root'; 

if (getenv('APP_ENV') === 'production') {

    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

} else {

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

$conn_str = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
$conn = pg_connect($conn_str);

if (!$conn) {
    if (getenv('APP_ENV') !== 'production') {
        $error_message = pg_last_error();
        die("Erro Crítico: Não foi possível conectar ao banco de dados PostgreSQL.<br>Detalhes: " . htmlspecialchars($error_message) . "<br>Verifique suas configurações em includes/config.php (dbname, user, password).");
    } else {
        error_log("Erro Fatal: Não foi possível conectar ao banco de dados PostgreSQL. DBName: {$dbname}, User: {$user}");
        die("Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.");
    }
}
?>
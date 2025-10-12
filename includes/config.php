<?php
// =======================================================
// ARQUIVO DE CONFIGURAÇÃO PARA POSTGRESQL
// =======================================================

// Configurações para PostgreSQL
$host = "localhost";
$port = "5432"; // Porta padrão do PostgreSQL
$dbname = "ajudaja"; // O nome do seu banco de dados
$user = "postgres"; // O usuário do seu PostgreSQL (padrão é 'postgres')
$password = "root"; // <-- IMPORTANTE: COLOQUE A SENHA DO SEU USUÁRIO POSTGRES AQUI

// String de conexão para o PostgreSQL
$conn_str = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

// Tenta estabelecer a conexão
$conn = pg_connect($conn_str);

// Verifica se a conexão falhou
if (!$conn) {
    die("Erro: Não foi possível conectar ao banco de dados PostgreSQL. Verifique suas configurações no arquivo config.php e se o serviço do PostgreSQL está rodando.");
}

// Configura o report de erros para ambiente de desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
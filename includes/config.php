<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ajudaja";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");
    if ($conn->connect_error) {
        throw new Exception("Conexão falhou: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// ... resto do código de conexão
?>
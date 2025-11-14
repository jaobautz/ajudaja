<?php
require_once 'session.php';      
require_once 'config.php';       
require_once 'autenticacao.php'; 
require_once 'validacao.php';    
require_once 'geocoding.php'; 

validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

// Coleta dados
$pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$cep = trim($_POST['cep'] ?? ''); // Coleta SÓ o CEP
$usuario_id = $_SESSION['usuario_id'];

// Validação (SÓ valida o CEP)
$erros = [];
$regras_titulo = ['obrigatorio', 'min:5', 'max:255'];
$regras_descricao = ['obrigatorio', 'min:20'];
$regras_urgencia = ['obrigatorio', 'in:Urgente,Pode Esperar,Daqui a uma Semana'];
$regras_categoria = ['obrigatorio', 'in:Cesta Básica,Carona,Apoio Emocional,Doação de Itens,Serviços Voluntários,Outros'];
$regras_cep = ['cep']; 

if ($erro = validar_campo($titulo, $regras_titulo)) $erros['titulo'] = $erro;
if ($erro = validar_campo($descricao, $regras_descricao)) $erros['descricao'] = $erro;
if ($erro = validar_campo($urgencia, $regras_urgencia)) $erros['urgencia'] = $erro;
if ($erro = validar_campo($categoria, $regras_categoria)) $erros['categoria'] = $erro;
if ($erro = validar_campo($cep, $regras_cep)) $erros['cep'] = $erro; 

if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros; $_SESSION['old_data'] = $_POST;
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id"); exit;
}
if (!$pedido_id) { /* ... erro ... */ }

// Limpa CEP
$cep_limpo = preg_replace('/[^0-9]/', '', $cep);

// Busca dados antigos do banco (para comparar CEP)
$sql_get_old = "SELECT cep, cidade, estado, latitude, longitude FROM pedidos WHERE id = $1 AND usuario_id = $2";
if (!@pg_prepare($conn, "get_current_geo", $sql_get_old)) { die("Erro DB: get_current_geo"); }
$result_old = pg_execute($conn, "get_current_geo", array($pedido_id, $usuario_id));
if (!$result_old || pg_num_rows($result_old) == 0) { $_SESSION['erro'] = "Permissão negada."; header("Location: " . BASE_URL . "/pages/dashboard.php"); exit; }
$dados_atuais = pg_fetch_assoc($result_old);
$cep_atual_limpo = preg_replace('/[^0-9]/', '', $dados_atuais['cep'] ?? '');
$cep_mudou = ($cep_limpo !== $cep_atual_limpo);

$cidade = $dados_atuais['cidade']; 
$estado = $dados_atuais['estado']; 
$latitude = $dados_atuais['latitude']; 
$longitude = $dados_atuais['longitude'];

if (!empty($cep_limpo) && $cep_mudou) {
    // CEP é válido e MUDOU -> Chama a API
    $geoData = getGeoDataFromCEP($cep_limpo);
    if ($geoData) {
        $cidade = $geoData['cidade'];
        $estado = $geoData['estado'];
        $latitude = $geoData['latitude'];
        $longitude = $geoData['longitude'];
    }
} elseif (empty($cep_limpo)) {
    // CEP foi apagado, limpa os dados
    $cidade = null; $estado = null; $latitude = null; $longitude = null;
}
// Se CEP não mudou, os dados antigos (já carregados acima) serão usados.

// Atualiza o banco
$sql = "UPDATE pedidos SET titulo = $1, descricao = $2, urgencia = $3, categoria = $4, cep = $5, cidade = $6, estado = $7, latitude = $8, longitude = $9 
        WHERE id = $10 AND usuario_id = $11";
if (!@pg_prepare($conn, "update_pedido_geo", $sql)) { die("Erro DB Prepare: " . pg_last_error($conn)); }
$result = @pg_execute($conn, "update_pedido_geo", array(
    $titulo, $descricao, $urgencia, $categoria,
    empty($cep_limpo) ? null : $cep, $cidade, $estado, $latitude, $longitude,
    $pedido_id, $usuario_id
));

if ($result) {
    $_SESSION['sucesso'] = 'Pedido atualizado com sucesso!';
    header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
} else {
    $_SESSION['erro'] = 'Erro ao executar a atualização: ' . pg_last_error($conn);
    $_SESSION['old_data'] = $_POST; 
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id");
}

pg_close($conn);
exit;
?>
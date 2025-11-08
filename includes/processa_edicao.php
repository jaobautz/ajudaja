<?php
require_once 'session.php';      
require_once 'config.php';       
require_once 'autenticacao.php'; 
require_once 'validacao.php';    
require_once 'geocoding.php'; // 1. Inclui o novo serviço

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
$cep = trim($_POST['cep'] ?? ''); 
$usuario_id = $_SESSION['usuario_id'];

// Validação Robusta (sem alterações)
$erros = [];
$regras_titulo = ['obrigatorio', 'min:5', 'max:255']; $regras_descricao = ['obrigatorio', 'min:20']; $regras_urgencia = ['obrigatorio', 'in:Urgente,Pode Esperar,Daqui a uma Semana']; $regras_categoria = ['obrigatorio', 'in:Cesta Básica,Carona,Apoio Emocional,Doação de Itens,Serviços Voluntários,Outros']; $regras_cep = ['cep']; 
if ($erro = validar_campo($titulo, $regras_titulo)) $erros['titulo'] = $erro;
if ($erro = validar_campo($descricao, $regras_descricao)) $erros['descricao'] = $erro;
if ($erro = validar_campo($urgencia, $regras_urgencia)) $erros['urgencia'] = $erro;
if ($erro = validar_campo($categoria, $regras_categoria)) $erros['categoria'] = $erro;
if ($erro = validar_campo($cep, $regras_cep)) $erros['cep'] = $erro; 

if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros; $_SESSION['old_data'] = $_POST;
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id"); exit;
}
if (!$pedido_id) { $_SESSION['erro'] = "ID inválido."; header("Location: " . BASE_URL . "/pages/dashboard.php"); exit; }

// Limpa o CEP
$cep_limpo = preg_replace('/[^0-9]/', '', $cep);

// 2. Lógica de Geocoding na Edição
$cidade = null; $estado = null; $latitude = null; $longitude = null;

// Busca o CEP atual do pedido no banco
$sql_get_cep = "SELECT cep FROM pedidos WHERE id = $1 AND usuario_id = $2";
if (!@pg_prepare($conn, "get_current_cep", $sql_get_cep)) { /* Erro */ }
$result_cep = pg_execute($conn, "get_current_cep", array($pedido_id, $usuario_id));
$cep_atual_limpo = preg_replace('/[^0-9]/', '', pg_fetch_result($result_cep, 0, 'cep') ?? '');
$cep_mudou = ($cep_limpo !== $cep_atual_limpo);

if (!empty($cep_limpo) && $cep_mudou) {
    // CEP é válido, foi preenchido e MUDOU -> Chama a API
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
// Se o CEP não mudou, não fazemos nada (os dados $cidade, $estado, etc.
// não serão atualizados no SQL abaixo, precisamos mudar isso)

// --- CORREÇÃO DE LÓGICA DE EDIÇÃO ---
// Devemos atualizar todos os campos, mesmo se o CEP não mudou.
// Se o CEP não mudou, precisamos buscar os dados antigos do banco para não apagá-los.
if (!$cep_mudou && !empty($cep_atual_limpo)) {
    $sql_get_geo = "SELECT cidade, estado, latitude, longitude FROM pedidos WHERE id = $1";
    if (!@pg_prepare($conn, "get_old_geo", $sql_get_geo)) { /* Erro */ }
    $result_geo = pg_execute($conn, "get_old_geo", array($pedido_id));
    $geo_antigo = pg_fetch_assoc($result_geo);
    $cidade = $geo_antigo['cidade'];
    $estado = $geo_antigo['estado'];
    $latitude = $geo_antigo['latitude'];
    $longitude = $geo_antigo['longitude'];
}
// =====================================

// 3. Atualiza o banco com os dados
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
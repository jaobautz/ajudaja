<?php
require_once 'session.php';      
require_once 'config.php';       
require_once 'autenticacao.php'; 
require_once 'validacao.php';    

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

// Validação Robusta (incluindo CEP)
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

// Se houver erros, redireciona
if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros;
    $_SESSION['old_data'] = $_POST;
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id");
    exit;
}

if (!$pedido_id) { 
    $_SESSION['erro'] = "ID de pedido inválido."; 
    header("Location: " . BASE_URL . "/pages/dashboard.php"); 
    exit; 
}

// Limpa o CEP
$cep_limpo = preg_replace('/[^0-9]/', '', $cep);

// Buscar dados atuais e chamar API se CEP mudou
$cidade = null; $estado = null; $latitude = null; $longitude = null;
$sql_get_cep = "SELECT cep, cidade, estado FROM pedidos WHERE id = $1 AND usuario_id = $2";
if (!@pg_prepare($conn, "get_current_cep", $sql_get_cep)) { /* Erro */ die("DB Prepare Error"); }
$result_cep = pg_execute($conn, "get_current_cep", array($pedido_id, $usuario_id));
$dados_atuais = pg_fetch_assoc($result_cep);
$cep_atual_limpo = preg_replace('/[^0-9]/', '', $dados_atuais['cep'] ?? '');
$cep_mudou = ($cep_limpo !== $cep_atual_limpo);

if (!empty($cep_limpo) && strlen($cep_limpo) === 8 && $cep_mudou) {
    // CEP válido, preenchido e MUDOU -> Chama a API
    $url_viacep = "https://viacep.com.br/ws/{$cep_limpo}/json/";
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url_viacep); curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch); $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($response && $httpcode == 200) {
        $data = json_decode($response, true);
        if (!isset($data['erro'])) { $cidade = $data['localidade'] ?? null; $estado = $data['uf'] ?? null; } 
        else { error_log("ViaCEP erro (Edição) CEP: {$cep_limpo}"); }
    } else { error_log("Falha ViaCEP (Edição) CEP: {$cep_limpo}. Code: {$httpcode}"); }
} elseif (!$cep_mudou && !empty($cep_atual_limpo)) {
    // CEP não mudou, reutiliza cidade/estado do banco
    $cidade = $dados_atuais['cidade']; $estado = $dados_atuais['estado'];
}
// Se CEP novo for vazio, cidade/estado ficam NULL. Lat/Lon sempre NULL por enquanto.

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
    if (pg_affected_rows($result) > 0 || !$cep_mudou) { 
        $_SESSION['sucesso'] = 'Pedido atualizado com sucesso!';
    }
    header("Location: " . BASE_URL . "/pages/pedido_detalhe.php?id=$pedido_id");
} else {
    $_SESSION['erro'] = 'Erro ao executar a atualização: ' . pg_last_error($conn);
    $_SESSION['old_data'] = $_POST; 
    header("Location: " . BASE_URL . "/pages/editar_pedido.php?id=$pedido_id");
}

pg_close($conn);
exit;
?>
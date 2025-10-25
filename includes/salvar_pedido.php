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

// Coleta os dados
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$urgencia = $_POST['urgencia'] ?? '';
$categoria = trim($_POST['categoria'] ?? '');
$whatsapp = $_POST['whatsapp'] ?? '';
$cep = trim($_POST['cep'] ?? ''); 

// Validação Robusta (incluindo CEP)
$erros = [];
$regras_titulo = ['obrigatorio', 'min:5', 'max:255'];
$regras_descricao = ['obrigatorio', 'min:20'];
$regras_urgencia = ['obrigatorio', 'in:Urgente,Pode Esperar,Daqui a uma Semana'];
$regras_categoria = ['obrigatorio', 'in:Cesta Básica,Carona,Apoio Emocional,Doação de Itens,Serviços Voluntários,Outros'];
$regras_whatsapp = ['obrigatorio', 'whatsapp'];
$regras_cep = ['cep']; // CEP é opcional, mas valida formato se preenchido

if ($erro = validar_campo($titulo, $regras_titulo)) $erros['titulo'] = $erro;
if ($erro = validar_campo($descricao, $regras_descricao)) $erros['descricao'] = $erro;
if ($erro = validar_campo($urgencia, $regras_urgencia)) $erros['urgencia'] = $erro;
if ($erro = validar_campo($categoria, $regras_categoria)) $erros['categoria'] = $erro;
if ($erro = validar_campo($whatsapp, $regras_whatsapp)) $erros['whatsapp'] = $erro;
if ($erro = validar_campo($cep, $regras_cep)) $erros['cep'] = $erro; 

// Se houver erros, redireciona de volta
if (!empty($erros)) {
    $_SESSION['erro_campos'] = $erros;
    $_SESSION['old_data'] = $_POST;
    header('Location: ' . BASE_URL . '/pages/cadastrar.php');
    exit;
}

// Limpa dados para salvar
$whatsapp_limpo = preg_replace('/[^0-9]/', '', $whatsapp);
$cep_limpo = preg_replace('/[^0-9]/', '', $cep);

// Chamar API ViaCEP e obter dados
$cidade = null;
$estado = null;
if (!empty($cep_limpo) && strlen($cep_limpo) === 8) {
    $url_viacep = "https://viacep.com.br/ws/{$cep_limpo}/json/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_viacep);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $httpcode == 200) {
        $data = json_decode($response, true);
        if (!isset($data['erro'])) {
            $cidade = $data['localidade'] ?? null;
            $estado = $data['uf'] ?? null;
        } else { error_log("ViaCEP retornou erro para CEP: {$cep_limpo}"); }
    } else { error_log("Falha ao conectar com ViaCEP para CEP: {$cep_limpo}. HTTP Code: {$httpcode}"); }
}
$latitude = null; $longitude = null; // V1 não busca coordenadas

// Insere no banco
$sql = "INSERT INTO pedidos (usuario_id, titulo, descricao, urgencia, categoria, whatsapp_numero, cep, cidade, estado, latitude, longitude) 
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11) RETURNING id";
if (!@pg_prepare($conn, "insert_pedido_geo", $sql)) { die("Erro DB Prepare: " . pg_last_error($conn)); }
$result = @pg_execute($conn, "insert_pedido_geo", array(
    $_SESSION['usuario_id'], $titulo, $descricao, $urgencia, $categoria, $whatsapp_limpo,
    empty($cep_limpo) ? null : $cep, $cidade, $estado, $latitude, $longitude
));

if ($result) {
    $new_id = pg_fetch_result($result, 0, 'id');
    $_SESSION['sucesso'] = 'Pedido cadastrado com sucesso!';
    header('Location: ' . BASE_URL . '/pages/pedido_detalhe.php?id=' . $new_id); 
} else {
    $_SESSION['erro'] = 'Erro ao salvar o pedido: ' . pg_last_error($conn);
    $_SESSION['old_data'] = $_POST;
    header('Location: ' . BASE_URL . '/pages/cadastrar.php');
}

pg_close($conn);
exit;
?>
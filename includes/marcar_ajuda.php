<?php
require_once 'session.php'; // Usa require_once
require_once 'config.php'; // Usa require_once
require_once 'autenticacao.php'; // Usa require_once

// Valida o token CSRF
validar_post_request();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Se não for POST, redireciona para a caixa de entrada
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

$conversa_id = filter_input(INPUT_POST, 'conversa_id', FILTER_VALIDATE_INT);
$usuario_logado_id = $_SESSION['usuario_id']; // ID do usuário que clicou no botão

if (!$conversa_id) {
    $_SESSION['erro'] = "ID de conversa inválido.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

// --- Lógica Principal ---
// Usaremos uma transação para garantir que ambas as atualizações (conversa e reputação) ocorram ou nenhuma ocorra.
pg_query($conn, "BEGIN"); // Inicia a transação

try {
    // 1. Buscar detalhes da conversa e verificar permissão
    //    Garante que o usuário logado é o CRIADOR do pedido associado a esta conversa.
    $sql_conversa = "SELECT c.usuario_criador_id, c.usuario_voluntario_id, c.status_conversa 
                     FROM conversas c 
                     WHERE c.id = $1";
    if (!@pg_prepare($conn, "get_conversa_for_rep", $sql_conversa)) {
        throw new Exception("Erro ao preparar consulta da conversa.");
    }
    $result_conversa = pg_execute($conn, "get_conversa_for_rep", array($conversa_id));

    if (!$result_conversa || pg_num_rows($result_conversa) == 0) {
        throw new Exception("Conversa não encontrada.");
    }

    $conversa = pg_fetch_assoc($result_conversa);

    // Verifica se quem clicou é realmente o dono do pedido
    if ($conversa['usuario_criador_id'] != $usuario_logado_id) {
        throw new Exception("Você não tem permissão para marcar esta ajuda.");
    }

    // Verifica se a ajuda já foi confirmada
    if ($conversa['status_conversa'] == 'Ajuda Confirmada') {
        throw new Exception("Esta ajuda já foi confirmada anteriormente.");
    }

    $voluntario_id = $conversa['usuario_voluntario_id']; // ID de quem vai ganhar o ponto

    // 2. Atualizar o status da conversa
    $sql_update_conversa = "UPDATE conversas SET status_conversa = 'Ajuda Confirmada' WHERE id = $1";
    if (!@pg_prepare($conn, "update_status_conversa", $sql_update_conversa)) {
        throw new Exception("Erro ao preparar atualização da conversa.");
    }
    $result_update_conversa = pg_execute($conn, "update_status_conversa", array($conversa_id));
    if (!$result_update_conversa) {
        throw new Exception("Erro ao atualizar o status da conversa.");
    }

    // 3. Atualizar a reputação do voluntário
    //    Usamos 'reputacao + 1' para incrementar atomicamente e evitar race conditions
    $sql_update_reputacao = "UPDATE usuarios SET reputacao = reputacao + 1 WHERE id = $1";
    if (!@pg_prepare($conn, "update_reputacao_voluntario", $sql_update_reputacao)) {
        throw new Exception("Erro ao preparar atualização da reputação.");
    }
    $result_update_reputacao = pg_execute($conn, "update_reputacao_voluntario", array($voluntario_id));
    if (!$result_update_reputacao) {
        throw new Exception("Erro ao atualizar a reputação do voluntário.");
    }

    // Se chegou até aqui, tudo deu certo!
    pg_query($conn, "COMMIT"); // Confirma as alterações no banco
    $_SESSION['sucesso'] = "Ajuda confirmada com sucesso! O voluntário recebeu +1 ponto de reputação.";

} catch (Exception $e) {
    // Se qualquer passo falhar, desfaz tudo
    pg_query($conn, "ROLLBACK"); // Desfaz as alterações
    $_SESSION['erro'] = $e->getMessage(); // Exibe a mensagem de erro específica
}

// Fecha a conexão e redireciona de volta para a caixa de entrada
if ($conn) { pg_close($conn); }
header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
exit;
?>
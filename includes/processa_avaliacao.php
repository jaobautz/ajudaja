<?php
require_once 'session.php'; 
require_once 'config.php'; 
require_once 'autenticacao.php'; 
require_once 'validacao.php'; 

// 1. Valida o Token CSRF
validar_post_request();

// 2. Garante que é um POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    exit;
}

// 3. Coleta de dados do formulário
$conversa_id = filter_input(INPUT_POST, 'conversa_id', FILTER_VALIDATE_INT);
$avaliado_id = filter_input(INPUT_POST, 'avaliado_id', FILTER_VALIDATE_INT); // O voluntário
$nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_INT);
$comentario = trim($_POST['comentario_avaliacao'] ?? '');
$avaliador_id = $_SESSION['usuario_id']; // O usuário logado (criador do pedido)

// 4. Validação dos dados
if (!$conversa_id || !$avaliado_id || $nota < 1 || $nota > 5) {
    $_SESSION['erro'] = "Dados de avaliação inválidos. Por favor, selecione uma nota de 1 a 5.";
    header('Location: ' . BASE_URL . '/pages/avaliar.php?conversa_id=' . $conversa_id);
    exit;
}
if (empty($comentario)) {
    $comentario = null; // Permite comentário nulo
}

// 5. Inicia a transação
pg_query($conn, "BEGIN"); 

try {
    // 5a. Verificar permissão (Confirma se o usuário logado é o criador da conversa e se ela não foi avaliada)
    $sql_check = "SELECT id FROM conversas 
                  WHERE id = $1 
                  AND usuario_criador_id = $2 
                  AND status_conversa = 'Aberta'";
    if (!@pg_prepare($conn, "check_perm_avaliacao", $sql_check)) { throw new Exception("Erro DB (Prepare Check)"); }
    $result_check = pg_execute($conn, "check_perm_avaliacao", array($conversa_id, $avaliador_id));

    if (!$result_check || pg_num_rows($result_check) == 0) {
        throw new Exception("Você não pode avaliar esta conversa ou ela já foi avaliada.");
    }

    // 5b. Inserir a avaliação na nova tabela 'avaliacoes'
    $sql_insert = "INSERT INTO avaliacoes (conversa_id, avaliador_id, avaliado_id, nota, comentario_avaliacao) 
                   VALUES ($1, $2, $3, $4, $5)";
    if (!@pg_prepare($conn, "insert_avaliacao", $sql_insert)) { throw new Exception("Erro DB (Prepare Insert)"); }
    $result_insert = pg_execute($conn, "insert_avaliacao", array($conversa_id, $avaliador_id, $avaliado_id, $nota, $comentario));
    
    if (!$result_insert) {
        throw new Exception("Erro ao salvar a avaliação.");
    }

    // 5c. Atualizar o status da conversa para 'Ajuda Confirmada'
    $sql_update = "UPDATE conversas SET status_conversa = 'Ajuda Confirmada' WHERE id = $1";
    if (!@pg_prepare($conn, "update_status_conversa_aval", $sql_update)) { throw new Exception("Erro DB (Prepare Update)"); }
    $result_update = pg_execute($conn, "update_status_conversa_aval", array($conversa_id));
    
    if (!$result_update) {
        throw new Exception("Erro ao atualizar o status da conversa.");
    }

    // 6. Se tudo deu certo:
    pg_query($conn, "COMMIT");
    $_SESSION['sucesso'] = "Avaliação enviada com sucesso! Obrigado por seu feedback.";
    header('Location: ' . BASE_URL . '/pages/caixa_entrada.php');
    
} catch (Exception $e) {
    // 7. Se algo falhou, desfaz tudo
    pg_query($conn, "ROLLBACK");
    $_SESSION['erro'] = "Erro: " . $e->getMessage();
    header('Location: ' . BASE_URL . '/pages/avaliar.php?conversa_id=' . $conversa_id);
}

if ($conn) { pg_close($conn); }
exit;
?>